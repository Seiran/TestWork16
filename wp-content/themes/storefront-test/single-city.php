<?php
$countries = get_the_terms(get_the_ID(), 'country');
if ($countries && !is_wp_error($countries)) {
    echo '<div class="country-info">';
    echo '<h3>Country:</h3>';
    foreach ($countries as $country) {
        echo '<a href="' . get_term_link($country) . '">' . $country->name . '</a>';
    }
    echo '</div>';
}
?>