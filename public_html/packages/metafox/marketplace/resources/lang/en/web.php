<?php

/* this is auto generated file */
return [
    'added_a_marketplace'                  => '{appName, select,
        feed {{
            parentType, select,
                group {{isShared, select,
                    1 {<profile>profile</profile>}
                    other {added a listing in <profile>profile</profile>}
                }}
                page {{isShared, select,
                    1 {<profile>profile</profile>}
                    other {added a listing in <profile>profile</profile>}
                }}
                other {added a listing}
        }}

        other {{isShared, select,
            0 {added a listing}
            other {{parentType, select,
                0 {added a listing}
                other {<profile>profile</profile>}
            }}
        }}
    }',
    'add_marketplace'                      => 'Add New Listing',
    'add_new_listing'                      => 'Add New Listing',
    'all_invoices'                         => 'Invoices',
    'all_listings'                         => 'All Listings',
    'amount'                               => 'Amount',
    'back_to_invoices'                     => 'Back to Invoices',
    'bought'                               => 'Bought',
    'buyer'                                => 'Buyer',
    'featured_listings'                    => 'Featured Listings',
    'filter_price'                         => '{min, select, 0{{max, select, 0{free} other{below {max}}}} other{{max, select, 0{above {min}} other{{min} to {max}}}}}',
    'invited_people'                       => 'Invited People',
    'invoices'                             => 'Invoices',
    'listing_detail'                       => 'Listing Detail',
    'listing_is_waiting_approve'           => 'This listing is waiting for approval',
    'listings'                             => 'Listings',
    'my_listings'                          => 'My Listings',
    'my_pending_listings'                  => 'My Pending Listings',
    'name_type_listing'                    => 'Listing',
    'name_type_marketplace'                => 'Marketplace',
    'no_marketplace_found'                 => 'No listings are found.',
    'no_marketplace_found_description'     => 'Add a new listing to establish your trading activities.',
    'no_marketplace_invoices_found'        => 'No invoices are found.',
    'no_one_has_been_invited'              => 'No one has been invited.',
    'payment_status'                       => 'Payment Status',
    'popular_listings'                     => 'Popular Listings',
    'price_is_not_available'               => 'Price is not available',
    'resource_name_lower_case_marketplace' => 'listing',
    'search_by_location'                   => 'Search by location...',
    'search_marketplace'                   => 'Search marketplace',
    'search_marketplaces'                  => 'Search marketplaces',
    'search_listings'                      => 'Search listings',
    'seller'                               => 'Seller',
    'sold_invoices'                        => 'Sold Invoices',
    'sponsored_listings'                   => 'Sponsored Listings',
    'tab_listing_all'                      => 'All Listings',
    'transaction_date'                     => 'Transaction Date',
    'visit_listing_site'                   => 'Visit Listing Site',
    'total_value_listings'                 => '{value, plural, =1{# listing} other{# listings}}',
];
