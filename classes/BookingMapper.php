<?php
class BookingMapper
{
    protected $db;
    protected const OPEN_HOUR = 9;
    protected const CLOSE_HOUR = 21;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getBookings(): array
    {
        $sql = 'SELECT `id`, `name`, `phone`, `time` FROM bookings ORDER BY `id` DESC';
        $stmt = $this->db->query($sql);
        $results = [];
        while ($row = $stmt->fetch()) {
            $results[] = new Booking($row);
        }
        return $results;
    }

    public function getAvailableHours(int $period_of_days = 7): array
    {
        $begin = new DateTime('+1 hour');
        $end = new DateTime("+$period_of_days days");

        $sql = 'SELECT `time` FROM bookings WHERE `time` BETWEEN NOW() AND NOW() + INTERVAL :period DAY';
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute(['period' => $period_of_days]);

        $unavailables = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $period = new DatePeriod(
            $begin,
            new DateInterval('PT1H'),
            $end
        );

        $availables = [];
        foreach ($period as $time) {
            $hour = (int) $time->format('G');
            $is_work_time = self::OPEN_HOUR <= $hour && $hour < self::CLOSE_HOUR;
            $is_available = !in_array($time->format('Y-m-d H:00:00'), $unavailables);

            if ($is_work_time && $is_available) {
                $availables[] = $time->format('Y-m-d H:00:00');
            }
        }

        return $availables;
    }

    public function save(Booking $booking)
    {
        $sql = 'INSERT INTO bookings (`name`, `phone`, `time`) VALUES (:name, :phone, :time)';
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            'name' => $booking->getName(),
            'phone' => $booking->getPhone(),
            'time' => $booking->getTime()
        ]);
        if (!$result) {
            throw new Exception('could not save record');
        }

        return $this->db->lastInsertId();
    }
}
