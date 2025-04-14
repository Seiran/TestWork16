<?php
global $wpdb;
$search_term = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';

$query = $wpdb->prepare("
    SELECT 
        terms.name AS country,
        posts.post_title AS city,
        MAX(CASE WHEN latmeta.meta_key = '_city_latitude' THEN latmeta.meta_value END) AS latitude,
        MAX(CASE WHEN lngmeta.meta_key = '_city_longitude' THEN lngmeta.meta_value END) AS longitude,
        MAX(CASE WHEN tempmeta.meta_key = '_city_temperature' THEN tempmeta.meta_value END) AS temperature
    FROM {$wpdb->posts} AS posts
    INNER JOIN {$wpdb->term_relationships} AS rel ON posts.ID = rel.object_id
    INNER JOIN {$wpdb->term_taxonomy} AS tax ON rel.term_taxonomy_id = tax.term_taxonomy_id
    INNER JOIN {$wpdb->terms} AS terms ON tax.term_id = terms.term_id
    LEFT JOIN {$wpdb->postmeta} AS latmeta ON posts.ID = latmeta.post_id AND latmeta.meta_key = '_city_latitude'
    LEFT JOIN {$wpdb->postmeta} AS lngmeta ON posts.ID = lngmeta.post_id AND lngmeta.meta_key = '_city_longitude'
    LEFT JOIN {$wpdb->postmeta} AS tempmeta ON posts.ID = tempmeta.post_id AND tempmeta.meta_key = '_city_temperature'
    WHERE 
        posts.post_type = 'city' 
        AND posts.post_status = 'publish'
        AND tax.taxonomy = 'country'
        AND (posts.post_title LIKE %s OR terms.name LIKE %s)
    GROUP BY posts.ID, terms.term_id
    ORDER BY country, city",
    '%' . $wpdb->esc_like($search_term) . '%',
    '%' . $wpdb->esc_like($search_term) . '%'
);

$results = $wpdb->get_results($query);
?>

<?php if (!empty($results)) : ?>

    <?php 
    // Хук перед таблицей
    do_action('before_cities_table', $results); 
    ?>
    
    <table class="weather-table">
        <thead>
            <tr>
                <th>Country</th>
                <th>City</th>
                <th>Latitude</th>
                <th>Longitude</th>
                <th>Temperature</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($results as $row) : ?>
                <tr>
                    <td><?php echo esc_html($row->country); ?></td>
                    <td><?php echo esc_html($row->city); ?></td>
                    <td><?php echo esc_html($row->latitude); ?></td>
                    <td><?php echo esc_html($row->longitude); ?></td>
                    <td>
                        <?php if ($row->temperature) : ?>
                            <?php echo round($row->temperature); ?>°C
                        <?php else : ?>
                            N/A
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <?php 
    // Хук после таблицы
    do_action('after_cities_table', $results); 
    ?>
    
<?php else : ?>
    <p>No cities found</p>
<?php endif; ?>