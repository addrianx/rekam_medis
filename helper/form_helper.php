<?php
/**
 * Helper form untuk menampilkan input dengan validasi error dan value sebelumnya.
 *
 * @param string $label Label yang ditampilkan
 * @param string $name Name field
 * @param array $errors Daftar error validasi
 * @param array $old Data input lama ($_SESSION['old'])
 * @param string $type Jenis input: text, date, textarea, select, checkbox, file
 * @param array $options Opsi (khusus select dan checkbox multiple)
 * @param bool $multiple Untuk checkbox (multiple values) atau file multiple
 */
function field($label, $name, $errors, $old, $type = 'text', $options = [], $multiple = false) {
    $error = $errors[$name] ?? '';
    $borderClass = $error ? 'border-red-500' : 'border-gray-500';

    echo "<div class='mb-4'>";
    echo "<label class='block font-medium text-gray-700 mb-1'>$label</label>";

    // Untuk checkbox multiple
    if ($type === 'checkbox' && $multiple && is_array($options)) {
        foreach ($options as $value => $display) {
            $checked = in_array($value, $old[$name] ?? []) ? 'checked' : '';
            echo "<label class='inline-flex items-center mr-4'>";
            echo "<input type='checkbox' name='{$name}[]' value='$value' $checked class='rounded border-gray-300 text-blue-600 shadow-sm focus:ring focus:ring-blue-500'>";
            echo "<span class='ml-2 text-sm text-gray-700'>$display</span>";
            echo "</label>";
        }
    }

    // Untuk checkbox single
    elseif ($type === 'checkbox') {
        $checked = !empty($old[$name]) ? 'checked' : '';
        echo "<label class='inline-flex items-center'>";
        echo "<input type='checkbox' name='$name' value='1' $checked class='rounded border-gray-300 text-blue-600 shadow-sm focus:ring focus:ring-blue-500'>";
        echo "<span class='ml-2 text-sm text-gray-700'>$label</span>";
        echo "</label>";
    }

    // Untuk select
    elseif ($type === 'select') {
        $value = $old[$name] ?? '';
        echo "<select name='$name' class='mt-1 w-full rounded-xl border-2 $borderClass px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500'>";
        echo "<option value=''>Pilih $label</option>";
        foreach ($options as $opt) {
            $selected = $value === $opt ? 'selected' : '';
            echo "<option value='$opt' $selected>$opt</option>";
        }
        echo "</select>";
    }

    // Untuk textarea
    elseif ($type === 'textarea') {
        $value = htmlspecialchars($old[$name] ?? '');
        echo "<textarea name='$name' rows='4' class='mt-1 w-full rounded-xl border-2 $borderClass px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500'>$value</textarea>";
    }

    // Untuk file upload
    elseif ($type === 'file') {
        $multipleAttr = $multiple ? 'multiple' : '';
        echo "<input type='file' name='" . ($multiple ? "{$name}[]" : $name) . "' class='mt-1 w-full text-sm text-gray-700' $multipleAttr>";
    }

    // Default input: text, date, etc.
    else {
        $value = htmlspecialchars($old[$name] ?? '');
        echo "<input type='$type' name='$name' value='$value' class='mt-1 w-full rounded-xl border-2 $borderClass px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500'>";
    }

    // Error message
    if ($error) {
        echo "<p class='text-red-600 text-sm mt-1'>$error</p>";
    }

    echo "</div>";
}
