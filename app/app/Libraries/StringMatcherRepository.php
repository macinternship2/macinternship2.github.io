<?php namespace App\Libraries;

/*
    StringMatcherRepository is very similar to
    importers/utils/import_helpers/string_matcher_repo.py
    but written in PHP
*/

class StringMatcherRepository
{
    public function __construct($json_path)
    {
        $this->string_matchers = [];
        $this->items_config = null;
        $this->json_path = $json_path;
        $this->json_dir = dirname($json_path) . '/';
    }

    public function getPathPrefixFor($item_id)
    {
        $this->loadConfig();
        $item = $this->items_config[$item_id];
        return $this->json_dir . $item['prefix'];
    }

    public function loadConfig()
    {
        if ($this->items_config === null) {
            $items_data = json_decode(file_get_contents($this->json_path), true);
            $items = [];
            // convert array to index by item id.
            foreach ($items_data as $item) {
                $items[$item['id']] = $item;
            }
            
            $this->items_config = $items;
        }
    }

    public function getItemIds()
    {
        $this->loadConfig();
        return array_keys($this->items_config);
    }

    public function appliesTo($s, $item_id)
    {
        if (isset($this->string_matchers[$item_id])) {
            $s_matcher = $this->string_matchers[$item_id];
        } else {
            $s_matcher = new StringMatcher($this->getPathPrefixFor($item_id));
            $this->string_matchers[$item_id] = $s_matcher;
        }

        return $s_matcher->appliesToName($s);
    }
}
