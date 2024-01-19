function create_comparison_posts() {
  // Get all posts in category "1"
  $category_1_posts = get_posts(array(
    'category' => 1,
    'posts_per_page' => -1, // get all posts
  ));

  // Loop through each post in category "1"
  foreach ($category_1_posts as $post) {
    // Get the ID and title of the post
    $id = $post->ID;
    $title = $post->post_title;

    // Loop through all other posts in category "1"
    foreach ($category_1_posts as $compare_post) {
      // Skip the current post
      if ($id == $compare_post->ID) continue;

      // Generate the title for the comparison post
      $compare_title = "$title vs " . $compare_post->post_title;

      // Check if a comparison post with this title already exists
      $comparison_post = get_page_by_title($compare_title, OBJECT, 'post');
      if ($comparison_post) continue; // skip if the post already exists

      // Only create the comparison post if the ID of the first post is smaller than the ID of the second post
      if ($id < $compare_post->ID) {
        // Create a new post with the comparison title and the "Comparisons" category
        wp_insert_post(array(
          'post_title' => $compare_title,
          'post_type' => 'post',
          'post_status' => 'publish',
          'post_category' => array(6), // category with ID 2 is "Comparisons"
          'page_template' => 'template-fullwidth.php', // use the "Comparisons" template
        ));
      }
    }
  }
}
add_action( 'publish_post', 'create_comparison_posts', 10, 2);
		   
function delete_comparison_posts($post_id) {
  // Check if the trashed post belongs to category 1
  if (has_category(1, $post_id)) {
    // Get the title of the trashed post
    $trashed_post = get_post($post_id);
    $trashed_post_title = $trashed_post->post_title;
  
    // Get all posts in category 6
    $category_6_posts = get_posts(array(
      'category' => 6,
      'post_status' => 'any',
	  'posts_per_page' => -1
    ));
    // Loop through the posts and delete any that contain the trashed post's title as a substring
    foreach ($category_6_posts as $post) {
	  var_dump($post->post_title);
      if (preg_match("/{$trashed_post_title}/i", $post->post_title)) {
        wp_delete_post($post->ID);
      }
    }
  }
}

add_action('wp_trash_post', 'delete_comparison_posts');
add_action('publish_to_draft', 'delete_comparison_posts');

function get_post_ids_from_comparison_permalink($permalink) {
  // Get the comparison post object from the permalink
  $comparison_post = get_page_by_path(basename($permalink), OBJECT, 'post');

  // Extract the titles of the compared posts from the post title
  $titles = explode(' vs ', $comparison_post->post_title);

  // Get the post objects for the compared posts
  $post1 = get_page_by_title($titles[0], OBJECT, 'post');
  $post2 = get_page_by_title($titles[1], OBJECT, 'post');

  // Return the IDs of the compared posts
  return array(
    'post1_id' => $post1->ID,
    'post2_id' => $post2->ID,
  );
}