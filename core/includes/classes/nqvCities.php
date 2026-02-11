<?php

class nqvCities {
    protected $name;
    protected $postal_code;
    protected $states_id;
    protected $latitude;
    protected $longitude;
    protected $is_active;

    public static function getMainSqlFieldProperty() {
        return 'name';
    }

    public static function getAll() {
        $sql = 'SELECT countries.name AS country, states.name AS state, cities.name AS name, cities.id AS `id` FROM cities INNER JOIN states ON cities.states_id = states.id INNER JOIN countries ON states.countries_id = countries.id ORDER BY countries.name ASC, states.name ASC, cities.name ASC;';
        $stmt = nqvDB::prepare($sql);
        $result = nqvDB::parseSelect($stmt);
        foreach ($result as $item) {
            $country = $item['country'];
            $state = $item['state'];

            $grouped[$country][$state][] = [
                'name' => $item['name'],
                'id' => $item['id']
            ];
        }
        #my_print($grouped);
        return $grouped;
    }
}