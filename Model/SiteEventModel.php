<?php

namespace Model;

use App\Helper\Security;
use DateInterval;
use DateTimeImmutable;
use DateTimeZone;
use PDO;

class SiteEventModel extends Model
{
    protected $table = 'site_events';
    private DateTimeZone $timezone;

    /**
     * Takvim türleri için varsayılan renk eşleşmeleri
     */
    private const CALENDAR_STYLES = [
        'genel' => ['color' => '#ffffff', 'bgColor' => '#1abc9c', 'borderColor' => '#1abc9c'],
        'toplanti' => ['color' => '#ffffff', 'bgColor' => '#3498db', 'borderColor' => '#3498db'],
        'bakim' => ['color' => '#ffffff', 'bgColor' => '#e67e22', 'borderColor' => '#e67e22'],
        'duyuru' => ['color' => '#ffffff', 'bgColor' => '#9b59b6', 'borderColor' => '#9b59b6'],
        'sosyal' => ['color' => '#ffffff', 'bgColor' => '#e74c3c', 'borderColor' => '#e74c3c'],
    ];

    public function __construct()
    {
        parent::__construct($this->table);
        $this->timezone = new DateTimeZone('Europe/Istanbul');
    }

    /**
     * Belirtilen tarih aralığı için etkinlikleri döndürür.
     */
    public function getEventsForRange(int $firmId, int $siteId, string $start, string $end, array $calendarIds = []): array
    {
        [$rangeStart, $rangeEnd] = $this->normaliseRange($start, $end, false);

        $sql = "SELECT id, calendar_key, title, description, location, start_at, end_at, is_all_day, bg_color, color, border_color
                FROM {$this->table}
                WHERE firm_id = :firm_id
                  AND site_id = :site_id
                  AND deleted_at IS NULL
                  AND start_at < :range_end
                  AND end_at > :range_start";

        $bindings = [
            ':firm_id' => $firmId,
            ':site_id' => $siteId,
            ':range_start' => $rangeStart,
            ':range_end' => $rangeEnd,
        ];

        if (!empty($calendarIds)) {
            $placeholders = [];
            foreach (array_values($calendarIds) as $index => $calendar) {
                $placeholder = ':cal_' . $index;
                $placeholders[] = $placeholder;
                $bindings[$placeholder] = $calendar;
            }
            $sql .= ' AND calendar_key IN (' . implode(', ', $placeholders) . ')';
        }

        $sql .= ' ORDER BY start_at ASC';

        $statement = $this->db->prepare($sql);
        foreach ($bindings as $placeholder => $value) {
            $statement->bindValue($placeholder, $value);
        }
        $statement->execute();

        $rows = $statement->fetchAll(PDO::FETCH_OBJ) ?: [];

        return array_map(function ($row) {
            return $this->mapRow($row);
        }, $rows);
    }

    /**
     * Yeni etkinlik kaydı oluşturur ve kaydedilen veriyi döndürür.
     */
    public function createEvent(array $payload, int $firmId, int $siteId, int $userId): array
    {
        $calendarKey = $payload['calendarId'];
        $style = self::CALENDAR_STYLES[$calendarKey] ?? self::CALENDAR_STYLES['genel'];

        [$startAt, $endAt] = $this->normaliseRange($payload['start'], $payload['end'], (bool) $payload['isAllDay']);

        $statement = $this->db->prepare("INSERT INTO {$this->table}
            (firm_id, site_id, calendar_key, title, description, location, start_at, end_at, is_all_day, color, bg_color, border_color, created_by, updated_by)
            VALUES
            (:firm_id, :site_id, :calendar_key, :title, :description, :location, :start_at, :end_at, :is_all_day, :color, :bg_color, :border_color, :created_by, :updated_by)");

        $statement->execute([
            ':firm_id' => $firmId,
            ':site_id' => $siteId,
            ':calendar_key' => $calendarKey,
            ':title' => $payload['title'],
            ':description' => $payload['description'] ?? null,
            ':location' => $payload['location'] ?? null,
            ':start_at' => $startAt,
            ':end_at' => $endAt,
            ':is_all_day' => (int) $payload['isAllDay'],
            ':color' => $style['color'],
            ':bg_color' => $style['bgColor'],
            ':border_color' => $style['borderColor'],
            ':created_by' => $userId,
            ':updated_by' => $userId,
        ]);

        $insertId = (int) $this->db->lastInsertId();

        return $this->findEventById($insertId, $firmId, $siteId);
    }

    /**
     * Etkinliği günceller ve güncel veriyi döndürür.
     */
    public function updateEvent(string $encryptedId, array $payload, int $firmId, int $siteId, int $userId): array
    {
        $eventId = $this->decryptId($encryptedId);

        if (!$eventId) {
            throw new \RuntimeException('Etkinlik bulunamadı.');
        }

        $existing = $this->findEventById($eventId, $firmId, $siteId);
        if (empty($existing)) {
            throw new \RuntimeException('Etkinlik bulunamadı.');
        }

        $calendarKey = $payload['calendarId'] ?? $existing['calendarId'];
        $style = self::CALENDAR_STYLES[$calendarKey] ?? self::CALENDAR_STYLES['genel'];
        $isAllDay = array_key_exists('isAllDay', $payload) ? (bool) $payload['isAllDay'] : (bool) $existing['isAllDay'];

        [$startAt, $endAt] = $this->normaliseRange(
            $payload['start'] ?? $existing['start'],
            $payload['end'] ?? $existing['end'],
            $isAllDay
        );

        $statement = $this->db->prepare("UPDATE {$this->table}
            SET calendar_key = :calendar_key,
                title = :title,
                description = :description,
                location = :location,
                start_at = :start_at,
                end_at = :end_at,
                is_all_day = :is_all_day,
                color = :color,
                bg_color = :bg_color,
                border_color = :border_color,
                updated_by = :updated_by,
                updated_at = NOW()
            WHERE id = :id AND firm_id = :firm_id AND site_id = :site_id AND deleted_at IS NULL");

        $statement->execute([
            ':calendar_key' => $calendarKey,
            ':title' => $payload['title'] ?? $existing['title'],
            ':description' => $payload['description'] ?? $existing['description'],
            ':location' => $payload['location'] ?? $existing['location'],
            ':start_at' => $startAt,
            ':end_at' => $endAt,
            ':is_all_day' => (int) $isAllDay,
            ':color' => $style['color'],
            ':bg_color' => $style['bgColor'],
            ':border_color' => $style['borderColor'],
            ':updated_by' => $userId,
            ':id' => $eventId,
            ':firm_id' => $firmId,
            ':site_id' => $siteId,
        ]);

        return $this->findEventById($eventId, $firmId, $siteId);
    }

    /**
     * Etkinliği siler (soft delete).
     */
    public function deleteEvent(string $encryptedId, int $firmId, int $siteId, int $userId): bool
    {
        $eventId = $this->decryptId($encryptedId);
        if (!$eventId) {
            throw new \RuntimeException('Etkinlik bulunamadı.');
        }

        $statement = $this->db->prepare("UPDATE {$this->table}
            SET deleted_at = NOW(), updated_by = :updated_by
            WHERE id = :id AND firm_id = :firm_id AND site_id = :site_id AND deleted_at IS NULL");

        $statement->execute([
            ':updated_by' => $userId,
            ':id' => $eventId,
            ':firm_id' => $firmId,
            ':site_id' => $siteId,
        ]);

        return (bool) $statement->rowCount();
    }

    /**
     * Geçerli takvim kategorilerini döndürür.
     */
    public static function allowedCalendars(): array
    {
        return array_keys(self::CALENDAR_STYLES);
    }

    /**
     * ID üzerinden etkinlik verisini döndürür.
     */
    private function findEventById(int $id, int $firmId, int $siteId): array
    {
        $statement = $this->db->prepare("SELECT id, calendar_key, title, description, location, start_at, end_at, is_all_day, bg_color, color, border_color
            FROM {$this->table}
            WHERE id = :id AND firm_id = :firm_id AND site_id = :site_id AND deleted_at IS NULL");

        $statement->execute([
            ':id' => $id,
            ':firm_id' => $firmId,
            ':site_id' => $siteId,
        ]);

        $row = $statement->fetch(PDO::FETCH_OBJ);
        if (!$row) {
            return [];
        }

        return $this->mapRow($row);
    }

    private function mapRow(object $row): array
    {
        $style = self::CALENDAR_STYLES[$row->calendar_key] ?? self::CALENDAR_STYLES['genel'];

        return [
            'id' => Security::encrypt($row->id),
            'calendarId' => $row->calendar_key,
            'title' => $row->title,
            'description' => $row->description,
            'location' => $row->location,
            'start' => $row->start_at,
            'end' => $row->end_at,
            'isAllDay' => (bool) $row->is_all_day,
            'bgColor' => $row->bg_color ?? $style['bgColor'],
            'color' => $row->color ?? $style['color'],
            'borderColor' => $row->border_color ?? $style['borderColor'],
        ];
    }

    private function normaliseRange(string $start, string $end, bool $allDay): array
    {
        $startDate = $this->createDate($start);
        $endDate = $this->createDate($end);

        if ($allDay) {
            $startDate = $startDate->setTime(0, 0, 0);
            $endDate = $endDate->setTime(0, 0, 0)->add(new DateInterval('P1D'));
        }

        if ($endDate <= $startDate) {
            $endDate = $startDate->add(new DateInterval('PT1H'));
        }

        return [
            $startDate->format('Y-m-d H:i:s'),
            $endDate->format('Y-m-d H:i:s'),
        ];
    }

    private function createDate(string $value): DateTimeImmutable
    {
        $value = trim($value);
        if ($value === '') {
            return new DateTimeImmutable('now', $this->timezone);
        }

        if (str_contains($value, 'T')) {
            return new DateTimeImmutable($value, $this->timezone);
        }

        return DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $value, $this->timezone)
            ?: new DateTimeImmutable($value, $this->timezone);
    }

    private function decryptId(string $encryptedId): ?int
    {
        $id = Security::decrypt($encryptedId);
        if (!$id) {
            return null;
        }
        return (int) $id;
    }
}
