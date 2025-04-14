<?php
get_header();
?>

<div class="wrap">
    <h1><?php the_title(); ?></h1>
    
    <div class="weather-search-box">
        <input type="text" id="citySearch" placeholder="Search cities...">
        <button id="searchButton">Search</button>
    </div>

    <div id="weatherTableContainer">
        <?php include 'weather-table-content.php'; ?>
    </div>
    
</div>

<?php
get_footer();