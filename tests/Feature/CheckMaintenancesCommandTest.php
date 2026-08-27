<?php

namespace Tests\Feature;

use App\Models\AppNotification;
use App\Models\Customer;
use App\Models\Maintenance;
use App\Models\Vehicle;
use App\Models\Workshop;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckMaintenancesCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::parse('2026-08-26 10:00:00', 'America/Sao_Paulo'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_classifies_maintenances_and_creates_notifications(): void
    {
        $this->seedWorkshop();
        [$overdue, $upcoming, $later, $mileage, $done] = $this->seedMaintenances();

        $this->artisan('maintenances:check')
            ->expectsOutputToContain('4 verificadas')
            ->assertSuccessful();

        $this->assertSame('Atrasada', $overdue->fresh()->status);
        $this->assertSame('Próxima', $upcoming->fresh()->status);
        $this->assertSame('Próxima', $later->fresh()->status);
        $this->assertSame('Atrasada', $mileage->fresh()->status);
        $this->assertSame('Concluída', $done->fresh()->status);

        $this->assertSame('Atrasada', $overdue->vehicle->fresh()->maintenance_status);
        $this->assertSame('Próxima', $upcoming->vehicle->fresh()->maintenance_status);

        $this->assertSame(3, AppNotification::query()->where('type', 'maintenance')->count());
        $this->assertTrue(AppNotification::query()->where('source', "maintenance:{$overdue->id}:Atrasada:2026-08-20")->exists());
        $this->assertTrue(AppNotification::query()->where('source', "maintenance:{$upcoming->id}:Próxima:2026-09-05:D15")->exists());
        $this->assertTrue(AppNotification::query()->where('source', "maintenance:{$mileage->id}:Atrasada:2026-10-01")->exists());
        $this->assertFalse(AppNotification::query()->where('source', "like", "maintenance:{$later->id}:%")->exists());
    }

    public function test_second_run_is_idempotent(): void
    {
        $this->seedWorkshop();
        $this->seedMaintenances();

        $this->artisan('maintenances:check')->assertSuccessful();
        $afterFirst = AppNotification::query()->count();
        $statuses = Maintenance::query()->orderBy('id')->pluck('status')->all();

        $this->artisan('maintenances:check')
            ->expectsOutputToContain('0 atualizadas, 0 notificações novas')
            ->assertSuccessful();

        $this->assertSame($afterFirst, AppNotification::query()->count());
        $this->assertSame($statuses, Maintenance::query()->orderBy('id')->pluck('status')->all());
    }

    public function test_skips_notifications_when_alerts_are_disabled(): void
    {
        $this->seedWorkshop(['maintenance_alerts' => false]);
        $this->seedMaintenances();

        $this->artisan('maintenances:check')->assertSuccessful();

        $this->assertSame(0, AppNotification::query()->count());
        $this->assertSame('Atrasada', Maintenance::query()->where('service_name', 'Revisão')->value('status'));
    }

    public function test_notifies_inactive_customers_once_per_month(): void
    {
        $this->seedWorkshop(['maintenance_alerts' => false, 'contact_reminders' => true]);
        $customer = Customer::query()->create([
            'name' => 'Diego Alves',
            'phone' => '(11) 98888-0009',
            'whatsapp' => '(11) 98888-0009',
            'email' => 'diego@example.com',
            'document' => '999.999.999-99',
            'last_visit' => '2025-12-01',
        ]);

        $this->artisan('maintenances:check')->assertSuccessful();
        $this->assertSame(1, AppNotification::query()->where('type', 'contact')->count());
        $this->assertTrue(AppNotification::query()->where('source', "customer:{$customer->id}:inactive:2026-08")->exists());

        $this->artisan('maintenances:check')->assertSuccessful();
        $this->assertSame(1, AppNotification::query()->where('type', 'contact')->count());
    }

    public function test_is_scheduled_daily(): void
    {
        $events = collect(app(\Illuminate\Console\Scheduling\Schedule::class)->events());

        $event = $events->first(
            fn ($event) => str_contains($event->command ?? '', 'maintenances:check'),
        );

        $this->assertNotNull($event);
        $this->assertSame('0 6 * * *', $event->expression);
        $this->assertSame('America/Sao_Paulo', $event->timezone);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function seedWorkshop(array $overrides = []): Workshop
    {
        return Workshop::query()->create(array_merge([
            'name' => 'Oficina Auto Center',
            'document' => '12.345.678/0001-90',
            'phone' => '(11) 3333-1212',
            'whatsapp' => '(11) 99999-1212',
            'email' => 'contato@autocenter.com.br',
            'address' => 'Av. das Oficinas, 250',
            'maintenance_alerts' => true,
            'contact_reminders' => false,
            'weekly_report' => false,
            'default_reminder_days' => 15,
        ], $overrides));
    }

    /**
     * @return array{0: Maintenance, 1: Maintenance, 2: Maintenance, 3: Maintenance, 4: Maintenance}
     */
    private function seedMaintenances(): array
    {
        $customer = Customer::query()->create([
            'name' => 'Ana Souza',
            'phone' => '(11) 98888-0001',
            'whatsapp' => '(11) 98888-0001',
            'email' => 'ana@example.com',
            'document' => '123.456.789-00',
            'last_visit' => '2026-02-01',
        ]);

        $overdueVehicle = $this->vehicle($customer, 'AAA1A11', 20000, '2026-08-20', 'Próxima');
        $upcomingVehicle = $this->vehicle($customer, 'BBB2B22', 40000, '2026-09-05', 'Próxima');
        $laterVehicle = $this->vehicle($customer, 'CCC3C33', 10000, '2026-12-01', 'Próxima');
        $mileageVehicle = $this->vehicle($customer, 'DDD4D44', 50000, '2026-10-01', 'Próxima');
        $doneVehicle = $this->vehicle($customer, 'EEE5E55', 8000, '2026-01-10', 'Concluída');

        return [
            $this->maintenance($overdueVehicle, 'Revisão', '2026-08-20', 30000, 'Próxima'),
            $this->maintenance($upcomingVehicle, 'Troca de óleo', '2026-09-05', 50000, 'Próxima'),
            $this->maintenance($laterVehicle, 'Alinhamento', '2026-12-01', 20000, 'Próxima'),
            $this->maintenance($mileageVehicle, 'Freios', '2026-10-01', 45000, 'Próxima'),
            $this->maintenance($doneVehicle, 'Primeira revisão', '2026-01-10', 10000, 'Concluída'),
        ];
    }

    private function vehicle(Customer $customer, string $plate, int $mileage, string $next, string $status): Vehicle
    {
        return Vehicle::query()->create([
            'customer_id' => $customer->id,
            'plate' => $plate,
            'brand' => 'Honda',
            'model' => 'Civic',
            'year' => 2020,
            'mileage' => $mileage,
            'next_maintenance' => $next,
            'maintenance_status' => $status,
        ]);
    }

    private function maintenance(Vehicle $vehicle, string $service, string $due, int $mileage, string $status): Maintenance
    {
        return Maintenance::query()->create([
            'customer_id' => $vehicle->customer_id,
            'vehicle_id' => $vehicle->id,
            'service_name' => $service,
            'due_date' => $due,
            'due_mileage' => $mileage,
            'status' => $status,
        ]);
    }
}
