<?php
/**
 * Created by PhpStorm.
 * User: florent
 * Date: 19/03/19
 * Time: 15:56.
 */

namespace App\View;

class CustomerTransmissionView
{
    /**
     * @var string
     */
    public $email;

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }
}
