<?php

use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Message;
use Phalcon\Mvc\Model\Validator\Inclusionin;
use Phalcon\Mvc\Model\Validator\Uniqueness;

class Robots extends Model
{
    public function validation()
    {
        $this->validate(new Inclusionin(
            [
                "field" => "type",
                "domain" => ["droid", "mechanical", "virtual"]
            ]
        ));

        $this->validate(new Uniqueness(
            [
                "field" => "name",
                "message" => "The robot name must be unique"
            ]
        ));

        if ($this->year < 0) {
            $this->appendMessage(new Message("The year cannot be less that zero"));
        }

        if ($this->validationHasFailed()) {
            return false;
        }
    }
}