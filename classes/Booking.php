<?php
class Booking
{
    protected $id;
    protected $name;
    protected $phone;
    protected $time;

    public function __construct(array $data)
    {
        if(isset($data['id'])) {
            $this->id = $data['id'];
        }
        $this->name = $data['name'];
        $this->phone = $data['phone'];
        $this->time = $data['time'];
    }

    public function __get($property)
    {
        if ($property == 'masked_phone') {
            return $this->getMaskedPhone();
        }
        elseif (property_exists($this, $property)) {
            return $this->$property;
        }
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function getMaskedPhone(string $mask = '*', int $show_last = 3): string
    {
        $length = strlen($this->phone);
        $unmasked_piece = substr($this->phone, $length - $show_last, $show_last);
        return str_pad($unmasked_piece, $length, $mask, STR_PAD_LEFT);
    }

    public function getTime(): string
    {
        return $this->time;
    }

    public function getFormattedTime(): string
    {
        return date_format(date_create_from_format('Y-m-d H:i:s', $this->time), 'm.d @ G') . ' val.';
    }
}
