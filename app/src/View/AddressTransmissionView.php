<?php
/**
 * Created by PhpStorm.
 * User: florent
 * Date: 19/03/19
 * Time: 15:42.
 */

namespace App\View;

class AddressTransmissionView
{
    public string $firstName;
    public string $lastName;
    public string $countryCode;
    public string $street;
    public string $city;
    public string $postcode;
    public string $details;
    public string $phoneNumber;

    public function getSocialReason(): string
    {
        return $this->firstName . ' ' . $this->lastName;
    }

    public function getAddress1(): string
    {
        $posReturnLine = strpos($this->street, "\n");

        if (false !== $posReturnLine && $posReturnLine < 31) {
            return mb_substr($this->street, 0, $posReturnLine, 'UTF-8');
        } else {
            return mb_substr($this->street, 0, 31, 'UTF-8');
        }
    }

    public function getAddress2(): string
    {
        $posReturnLine = strpos($this->street, "\n");

        if (false !== $posReturnLine && $posReturnLine < 31) {
            return mb_substr($this->street, $posReturnLine, 63 - (31 - $posReturnLine), 'UTF-8');
        } else {
            return mb_substr($this->street, 31, 63, 'UTF-8');
        }
    }

    public function getVille(): string
    {
        return $this->city;
    }

    public function getCodePostal(): string
    {
        return $this->postcode;
    }

    public function getCodePays(): string
    {
        return $this->countryCode;
    }

    public function getDetails(): string
    {
        return mb_substr($this->details, 0, 32, 'UTF-8');
    }

    public function getTelPortable(): string
    {
        return $this->phoneNumber;
    }

    public function getPerson(): string
    {
        return $this->firstName . ' ' . $this->lastName;
    }
}
