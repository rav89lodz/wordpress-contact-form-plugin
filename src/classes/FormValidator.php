<?php

namespace ContactPlugin\src\classes;

class FormValidator
{
    public function sanitize_data_array($data) {
        $array = [];
        foreach ((array) $data as $label => $value) {
            $array[$label] = $this->validate($value, $label);
        }
        return $array;
    }

    private function validate($field, $type) {
        switch ($type) {
            case 'email':
                return sanitize_email($field);
            case 'message':
                return sanitize_textarea_field($field);
            default:
                return sanitize_text_field($field);
        }
    }
}