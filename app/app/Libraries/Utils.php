<?php namespace App\Libraries;

class Utils
{
    // Used by
    // LocationSearchController and InternalFeaturesController.
    public static function compareByName($named1, $named2)
    {
        return strcasecmp($named1->name, $named2->name);
    }
}
