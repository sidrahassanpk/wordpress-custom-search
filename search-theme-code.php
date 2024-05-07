<?php
/*
Plugin Name: Search Theme Code
Description: A plugin to search for specific text within the current active theme files.
Version: 1.0
Author: Sidra Hassan
*/

// Activation hook
register_activation_hook(__FILE__, 'custom_theme_search_activate');
// Deactivation hook
register_deactivation_hook(__FILE__, 'custom_theme_search_deactivate');

function custom_theme_search_activate() {
    // Activation code here
}

function custom_theme_search_deactivate() {
    // Deactivation code here
}

// Add a menu page for the plugin
add_action('admin_menu', 'custom_theme_search_menu');
function custom_theme_search_menu() {
    add_menu_page('Search Theme Code', 'Search Theme Code', 'manage_options', 'custom_theme_search', 'custom_theme_search_page');
}

// Search through the theme files
function search_theme_files($search_text) {
    $theme_directory = get_template_directory();
    $results = array();

    // Recursive function to search through the files in the theme directory
    function search_files_recursive($dir, $search_text, &$results) {
        $files = scandir($dir);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;
            $path = $dir . '/' . $file;
            if (is_dir($path)) {
                search_files_recursive($path, $search_text, $results);
            } else {
                $file_content = file_get_contents($path);
                if (strpos($file_content, $search_text) !== false) {
                    $results[] = $path;
                }
            }
        }
    }

    search_files_recursive($theme_directory, $search_text, $results);

    return $results;
}

// Render the plugin page
function custom_theme_search_page() {
    if (isset($_POST['search_text'])) {
        $search_text = sanitize_text_field($_POST['search_text']);
        $results = search_theme_files($search_text);

        if (!empty($results)) {
            echo "<div id='message' class='updated below-h2'><p>The search word '$search_text' is found in these files.</p></div>";
            echo "<ul>";
            foreach ($results as $result) {
                echo "<li><a href='javascript:;' class='search-result-link' data-path='$result'>$result</a></li>";
            }
            echo "</ul>";
        } else {
            echo "<div id='message' class='updated below-h2'><p>Sorry, no results. Try again.</p></div>";
        }
    }

    // Display the search form
    ?>
    <div class="wrap">
        <h2>Theme Search</h2>
        <form action="" method="post">
            <label for="search_text">Search Text:</label>
            <input type="text" id="search_text" name="search_text" value="<?php echo isset($_POST['search_text']) ? $_POST['search_text'] : ''; ?>" />
            <p><input type="submit" name="action" value="Search" class="button button-primary" /></p>
        </form>
    </div>

    <script>
    // Add JavaScript to handle the click event for each search result link
    const links = document.querySelectorAll('.search-result-link');
    links.forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const filePath = e.target.dataset.path;
            const themeName = filePath.split('/themes/')[1].split('/')[0];
            const fileName = filePath.split('/themes/')[1].split('/').slice(1).join('/');
            const url = `<?php echo admin_url('theme-editor.php'); ?>?file=${fileName}&theme=${themeName}`;
            window.open(url, '_blank'); // Open the link in a new page
        });
    });
	</script>
    <?php
}
