<?php

/* this is auto generated file */
return [
    'added_a_blog'                  => '{appName, select,
        feed {{
            parentType, select,
                group {{isShared, select, 0 {added a blog in <profile>profile</profile>} other {<profile>profile</profile>}}}
                page {{isShared, select, 0 {added a blog in <profile>profile</profile>} other {<profile>profile</profile>}}}
                other {added a blog}
        }}

        other {{isShared, select,
            1 {{parentType, select,
                0 {added a blog}
                other {<profile>profile</profile>}
            }}
            other {added a blog}
        }}
    }',
    'add_new_blog'                  => 'Add New Blog',
    'all_blogs'                     => 'All Blogs',
    'approve'                       => 'Approve',
    'blog_is_waiting_approve'       => 'This blog is waiting for approval',
    'blogs'                         => 'Blogs',
    'decline'                       => 'Decline',
    'draft_tag'                     => '[Draft]',
    'featured_blogs'                => 'Featured Blogs',
    'global_search_blog_no_result'  => 'No blogs are found.',
    'my_blogs'                      => 'My Blogs',
    'name_type_blog'                => 'Blog',
    'no_blog_found'                 => 'No blogs are found.',
    'no_blogs_found'                => 'No blogs are found.',
    'no_blog_found_description'     => 'Create a new blog for other people to read together.',
    'no_landing_blog_found'         => 'No blogs are found.',
    'no_my_blog_found'              => 'No blogs are found.',
    'popular_blogs'                 => 'Popular Blogs',
    'resource_name_lower_case_blog' => 'blog',
    'search_blogs'                  => 'Search blogs',
    'sponsored_blogs'               => 'Sponsored Blogs',
    'total_value_blogs'             => '{value, plural, =1{# blog} other{# blogs} }',
];
