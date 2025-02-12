<?php

require_once WorkingNYC\timber_post('Jobs');
require_once WorkingNYC\timber_post('Programs');

// Get the search term
$term = (isset($_GET['s'])) ? $_GET['s'] : '';

// Create query
$wp_query = new WP_Query(array(
  's' => $term,
  'post_type' => 'any'
));

// Redo relevanssi query and get posts in Timber format.
// This block could be made more efficient.
$relevanssi_query = relevanssi_do_query($wp_query);
$wp_query_ids = wp_list_pluck($wp_query->posts, 'ID');
$posts = Timber::get_posts($wp_query_ids);
$job_or_program_posts = array_filter($posts, function($p) {
  return $p->post_type == 'programs' || $p->post_type == 'jobs';
});

// Set Context
$context = Timber::get_context();
$context['term'] = $term;
$context['posts'] = array_map(function($p) {
  if ($p->post_type == 'programs') {
    return new WorkingNYC\Programs($p);
  } else {
    return new WorkingNYC\Jobs($p);
  }
}, $job_or_program_posts);
// $context['language'] = ICL_LANGUAGE_CODE;

// Render view
$templates = array('search.twig');
Timber::render($templates, $context);
