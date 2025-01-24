<?php
/**
 * Plugin Name: Random Date Changer
 * Description: Automatically assigns random publish dates to posts in WordPress within a given range.
 * Version: 1.0
 * Author: Tuhin Bairagi
 * Author URI: https://tuhinbairagi.com/
 * License: GPLv2 or later
 */

// Hook to add a menu in the WordPress admin panel
add_action('admin_menu', 'random_date_publish_menu');

// Function to create the menu
function random_date_publish_menu() {
    add_menu_page(
        'Random Date Publish', // Title of the page
        'Random Publish',      // Text in the menu
        'manage_options',      // Permission required
        'random-date-publish', // Unique slug for the menu
        'random_date_publish_page', // Function to display the content of the page
        'dashicons-calendar-alt', // Icon for the menu
        20                      // Position in the menu
    );
}

// Function to display the plugin's admin page
function random_date_publish_page() {
    // Check if the form is submitted
    if (isset($_POST['submit_dates'])) {
        $start_date = sanitize_text_field($_POST['start_date']);
        $end_date = sanitize_text_field($_POST['end_date']);
        $gap = intval($_POST['gap']);

        if ($start_date && $end_date && $gap > 0) {
            random_date_assign_posts($start_date, $end_date, $gap);
            echo '<div class="updated"><p>🎉 Success! All draft posts are now published with random dates!</p></div>';
        } else {
            echo '<div class="error"><p>⚠️ Error! Please make sure all fields are filled in correctly.</p></div>';
        }
    }

    // Display the form
    ?>
    <div class="wrap">
        <h1>Random Date Publish</h1>
        <form method="POST">
            <label for="start_date">Start Date:</label>
            <input type="date" name="start_date" id="start_date" required>
            <br><br>
            <label for="end_date">End Date:</label>
            <input type="date" name="end_date" id="end_date" required>
            <br><br>
            <label for="gap">Date Gap (in days):</label>
            <input type="number" name="gap" id="gap" min="1" required>
            <br><br>
            <input type="submit" name="submit_dates" class="button-primary" value="Assign Dates">
        </form>
    </div>
    <?php
}

// Function to assign random publish dates to posts
function random_date_assign_posts($start_date, $end_date, $gap) {
    global $wpdb;

    // Fetch all unpublished posts (drafts)
    $posts = $wpdb->get_results("
        SELECT ID FROM {$wpdb->posts} 
        WHERE post_status = 'draft'
    ");

    if ($posts) {
        $current_date = strtotime($start_date);
        $end_date = strtotime($end_date);

        foreach ($posts as $post) {
            if ($current_date > $end_date) {
                break;
            }

            $random_date = date('Y-m-d H:i:s', $current_date);

            // Update the post's date and publish it
            $wpdb->update(
                $wpdb->posts,
                [
                    'post_date' => $random_date,
                    'post_date_gmt' => $random_date,
                    'post_status' => 'publish'
                ],
                ['ID' => $post->ID]
            );

            // Increment the date by the specified gap
            $current_date = strtotime("+$gap days", $current_date);
        }
    }
}
?>
