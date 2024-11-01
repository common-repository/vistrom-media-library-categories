<?php

class VistromMediaHelpers
{
    /**
     * Filter and sanitize taxonomy terms ids
     *
     * @param array        $termIds  The term ids.
     * @param \WP_Taxonomy $taxonomy The taxonomy.
     *
     * @return array
     */
    public static function filter_valid_taxonomy_term_keys($termIds, $taxonomy)
    {
        if (!is_array($termIds) || empty($termIds)) {
            return [];
        }

        if ($taxonomy->hierarchical) {
            // Hierarchical taxonomies require list of term ids
            $termKeys = wp_parse_id_list($termIds);
        } else {
            // Flat taxonomies requires slugs
            $termKeys = array_map(function ($termId) use ($taxonomy) {
                $term = get_term($termId);

                if ($term && term_exists($term, $taxonomy->name)) {
                    return $term->slug;
                }

                return null;
            }, wp_parse_id_list($termIds));
        }

        return array_filter($termKeys, function ($key) use ($taxonomy) {
            return term_exists($key, $taxonomy->name);
        });
    }
}
new VistromMediaHelpers();
