<?php
class Form {
    public function open($action, $method = 'POST', $attributes = []) {
        $attrs = '';
        foreach ($attributes as $key => $value) {
            $attrs .= " $key=\"$value\"";
        }
        return "<form action=\"$action\" method=\"$method\"$attrs class='space-y-4'>";
    }

    public function close() {
        return '</form>';
    }

    public function input($name, $label, $type = 'text', $attributes = []) {
        $attrs = '';
        foreach ($attributes as $key => $value) {
            $attrs .= " $key=\"$value\"";
        }
        
        $html = "<div class='form-group'>";
        $html .= "<label for='$name' class='block text-sm font-medium mb-1'>$label</label>";
        $html .= "<input type='$type' name='$name' id='$name'$attrs class='w-full px-3 py-2 border rounded-md'>";
        $html .= "</div>";
        
        return $html;
    }

    public function select($name, $label, $options, $attributes = []) {
        $attrs = '';
        foreach ($attributes as $key => $value) {
            $attrs .= " $key=\"$value\"";
        }
        
        $html = "<div class='form-group'>";
        $html .= "<label for='$name' class='block text-sm font-medium mb-1'>$label</label>";
        $html .= "<select name='$name' id='$name'$attrs class='w-full px-3 py-2 border rounded-md'>";
        
        foreach ($options as $value => $text) {
            $selected = isset($attributes['value']) && $attributes['value'] == $value ? 'selected' : '';
            $html .= "<option value='$value' $selected>$text</option>";
        }
        
        $html .= "</select>";
        $html .= "</div>";
        
        return $html;
    }

    public function textarea($name, $label, $attributes = []) {
        $attrs = '';
        foreach ($attributes as $key => $value) {
            $attrs .= " $key=\"$value\"";
        }
        
        $html = "<div class='form-group'>";
        $html .= "<label for='$name' class='block text-sm font-medium mb-1'>$label</label>";
        $html .= "<textarea name='$name' id='$name'$attrs class='w-full px-3 py-2 border rounded-md'></textarea>";
        $html .= "</div>";
        
        return $html;
    }

    public function checkbox($name, $label, $value = '1', $attributes = []) {
        $attrs = '';
        foreach ($attributes as $key => $val) {
            $attrs .= " $key=\"$val\"";
        }
        
        $html = "<div class='form-group flex items-center'>";
        $html .= "<input type='checkbox' name='$name' id='$name' value='$value'$attrs class='mr-2'>";
        $html .= "<label for='$name' class='text-sm font-medium'>$label</label>";
        $html .= "</div>";
        
        return $html;
    }

    public function radio($name, $label, $value, $attributes = []) {
        $attrs = '';
        foreach ($attributes as $key => $val) {
            $attrs .= " $key=\"$val\"";
        }
        
        $html = "<div class='form-group flex items-center'>";
        $html .= "<input type='radio' name='$name' id='{$name}_{$value}' value='$value'$attrs class='mr-2'>";
        $html .= "<label for='{$name}_{$value}' class='text-sm font-medium'>$label</label>";
        $html .= "</div>";
        
        return $html;
    }

    public function submit($text, $attributes = []) {
        $attrs = '';
        foreach ($attributes as $key => $value) {
            $attrs .= " $key=\"$value\"";
        }
        
        return "<button type='submit'$attrs class='btn btn-primary'>$text</button>";
    }

    public function cancel($url, $text = 'Cancel', $attributes = []) {
        $attrs = '';
        foreach ($attributes as $key => $value) {
            $attrs .= " $key=\"$value\"";
        }
        
        return "<a href='$url'$attrs class='btn btn-outline ml-2'>$text</a>";
    }
} 