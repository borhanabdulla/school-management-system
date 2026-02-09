<?php

namespace Tests\Unit\Domains\Academic;

use Tests\TestCase;
use App\Domains\Academic\Notifications\WeeklyReadinessReminder;
use App\Domains\Academic\Data\ReadinessItem;
use App\Domains\Academic\Data\Enums\ReadinessSeverity;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;

/**
 * Unit tests for WeeklyReadinessReminder notification
 */
class WeeklyReadinessReminderTest extends TestCase
{
    /** @test */
    public function it_can_be_created_with_items()
    {
        // Arrange
        $items = collect([
            ReadinessItem::blocking(
                'test_blocking',
                'Blocking Item',
                'This is blocking',
                5
            ),
            ReadinessItem::warning(
                'test_warning',
                'Warning Item',
                'This is a warning',
                3
            ),
        ]);

        // Act
        $notification = new WeeklyReadinessReminder(
            items: $items,
            yearName: '2082/2083',
            yearId: 1
        );

        // Assert
        $this->assertNotNull($notification);
        $this->assertEquals('2082/2083', $this->getProperty($notification, 'yearName'));
        $this->assertEquals(1, $this->getProperty($notification, 'yearId'));
        $this->assertEquals(2, $this->getProperty($notification, 'items')->count());
    }

    /** @test */
    public function it_returns_correct_via_channels()
    {
        // Arrange
        $notification = new WeeklyReadinessReminder(
            items: collect(),
            yearName: '2082/2083',
            yearId: 1
        );

        $notifiable = new class {
            public function routeNotificationFor($driver, $notification = null) {
                return 'test@example.com';
            }
        };

        // Act
        $channels = $notification->via($notifiable);

        // Assert
        $this->assertContains('mail', $channels);
        $this->assertContains('database', $channels);
    }

    /** @test */
    public function it_has_correct_type_in_array_representation()
    {
        // Arrange
        $items = collect([
            ReadinessItem::blocking('test', 'Label', 'Message', 5)
        ]);

        $notification = new WeeklyReadinessReminder(
            items: $items,
            yearName: '2082/2083',
            yearId: 1
        );

        $notifiable = new class {
            public $name = 'Test User';
        };

        // Act
        $array = $notification->toArray($notifiable);

        // Assert
        $this->assertArrayHasKey('type', $array);
        $this->assertEquals('weekly_readiness_reminder', $array['type']);
        $this->assertArrayHasKey('year_id', $array);
        $this->assertArrayHasKey('year_name', $array);
        $this->assertArrayHasKey('blocking_count', $array);
        $this->assertArrayHasKey('warning_count', $array);
    }

    /** @test */
    public function it_counts_blocking_and_warning_items_correctly()
    {
        // Arrange
        $items = collect([
            ReadinessItem::blocking('b1', 'B1', 'Message', 1),
            ReadinessItem::blocking('b2', 'B2', 'Message', 2),
            ReadinessItem::warning('w1', 'W1', 'Message', 3),
        ]);

        $notification = new WeeklyReadinessReminder(
            items: $items,
            yearName: '2082/2083',
            yearId: 1
        );

        $notifiable = new class {
            public $name = 'Test User';
        };

        // Act
        $array = $notification->toArray($notifiable);

        // Assert
        $this->assertEquals(2, $array['blocking_count']);
        $this->assertEquals(1, $array['warning_count']);
    }

    /** @test */
    public function it_includes_item_details_in_array()
    {
        // Arrange
        $items = collect([
            ReadinessItem::blocking(
                'test_key',
                'Test Label',
                'Test Message',
                10,
                'test.route',
                ['param' => 'value']
            )
        ]);

        $notification = new WeeklyReadinessReminder(
            items: $items,
            yearName: '2082/2083',
            yearId: 1
        );

        $notifiable = new class {
            public $name = 'Test User';
        };

        // Act
        $array = $notification->toArray($notifiable);

        // Assert
        $this->assertArrayHasKey('items', $array);
        $this->assertCount(1, $array['items']);

        $item = $array['items'][0];
        $this->assertEquals('test_key', $item['key']);
        $this->assertEquals('blocking', $item['severity']);
        $this->assertEquals('Test Label', $item['label']);
        $this->assertEquals('Test Message', $item['message']);
        $this->assertEquals(10, $item['count']);
    }

    /**
     * Helper method to get private property
     */
    protected function getProperty(object $object, string $propertyName): mixed
    {
        $reflection = new \ReflectionClass($object);
        $property = $reflection->getProperty($propertyName);
        $property->setAccessible(true);
        return $property->getValue($object);
    }
}
