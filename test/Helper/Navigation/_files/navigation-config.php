<?php

declare(strict_types=1);

return [
    'nav_test1' => [
        'zym'   => [
            'label' => 'Zym',
            'uri'   => 'http://www.zym-project.com/',
            'order' => '100',
        ],
        'page1' => [
            'label' => 'Page 1',
            'uri'   => 'page1',
            'pages' => [
                'page1_1' => [
                    'label' => 'Page 1.1',
                    'uri'   => 'page1/page1_1',
                ],
            ],
        ],
        'page2' => [
            'label' => 'Page 2',
            'uri'   => 'page2',
            'pages' => [
                'page2_1' => [
                    'label' => 'Page 2.1',
                    'uri'   => 'page2/page2_1',
                ],
                'page2_2' => [
                    'label' => 'Page 2.2',
                    'uri'   => 'page2/page2_2',
                    'pages' => [
                        'page2_2_1' => [
                            'label' => 'Page 2.2.1',
                            'uri'   => 'page2/page2_2/page2_2_1',
                        ],
                        'page2_2_2' => [
                            'label'  => 'Page 2.2.2',
                            'uri'    => 'page2/page2_2/page2_2_2',
                            'active' => '1',
                        ],
                    ],
                ],
                'page2_3' => [
                    'label' => 'Page 2.3',
                    'uri'   => 'page2/page2_3',
                    'pages' => [
                        'page2_3_1' => [
                            'label' => 'Page 2.3.1',
                            'uri'   => 'page2/page2_3/page2_3_1',
                        ],
                        'page2_3_2' => [
                            'label'   => 'Page 2.3.2',
                            'uri'     => 'page2/page2_3/page2_3_2',
                            'visible' => '0',
                            'pages'   => [
                                'page2_3_2_1' => [
                                    'label'  => 'Page 2.3.2.1',
                                    'uri'    => 'page2/page2_3/page2_3_2/1',
                                    'active' => '1',
                                ],
                                'page2_3_2_2' => [
                                    'label'  => 'Page 2.3.2.2',
                                    'uri'    => 'page2/page2_3/page2_3_2/2',
                                    'active' => '1',
                                    'pages'  => [
                                        'page_2_3_2_2_1' => [
                                            'label'  => 'Ignore',
                                            'uri'    => '#',
                                            'active' => '1',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'page2_3_3' => [
                            'label'    => 'Page 2.3.3',
                            'uri'      => 'page2/page2_3/page2_3_3',
                            'resource' => 'admin_foo',
                            'pages'    => [
                                'page2_3_3_1' => [
                                    'label'  => 'Page 2.3.3.1',
                                    'uri'    => 'page2/page2_3/page2_3_3/1',
                                    'active' => '1',
                                ],
                                'page2_3_3_2' => [
                                    'label'    => 'Page 2.3.3.2',
                                    'uri'      => 'page2/page2_3/page2_3_3/2',
                                    'resource' => 'guest_foo',
                                    'active'   => '1',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'page3' => [
            'label' => 'Page 3',
            'uri'   => 'page3',
            'pages' => [
                'page3_1' => [
                    'label'    => 'Page 3.1',
                    'uri'      => 'page3/page3_1',
                    'resource' => 'guest_foo',
                ],
                'page3_2' => [
                    'label'    => 'Page 3.2',
                    'uri'      => 'page3/page3_2',
                    'resource' => 'member_foo',
                    'pages'    => [
                        'page3_2_1' => [
                            'label' => 'Page 3.2.1',
                            'uri'   => 'page3/page3_2/page3_2_1',
                        ],
                        'page3_2_2' => [
                            'label'     => 'Page 3.2.2',
                            'uri'       => 'page3/page3_2/page3_2_2',
                            'resource'  => 'admin_foo',
                            'privilege' => 'read',
                        ],
                    ],
                ],
                'page3_3' => [
                    'label'    => 'Page 3.3',
                    'uri'      => 'page3/page3_3',
                    'resource' => 'special_foo',
                    'pages'    => [
                        'page3_3_1' => [
                            'label'   => 'Page 3.3.1',
                            'uri'     => 'page3/page3_3/page3_3_1',
                            'visible' => '0',
                        ],
                        'page3_3_2' => [
                            'label'    => 'Page 3.3.2',
                            'uri'      => 'page3/page3_3/page3_3_2',
                            'resource' => 'admin_foo',
                        ],
                    ],
                ],
            ],
        ],
        'home'  => [
            'label' => 'Home',
            'uri'   => 'index',
            'title' => 'Go home',
            'order' => '-100',
        ],
    ],
    'nav_test2' => [
        'site1' => [
            'label'      => 'Site 1',
            'uri'        => 'site1',
            'changefreq' => 'daily',
            'priority'   => '0.9',
        ],
        'site2' => [
            'label'   => 'Site 2',
            'uri'     => 'site2',
            'active'  => '1',
            'lastmod' => 'earlier',
        ],
        'site3' => [
            'label'      => 'Site 3',
            'uri'        => 'site3',
            'changefreq' => 'often',
        ],
    ],
    'nav_test3' => [
        'page1' => [
            'label' => 'Page 1',
            'uri'   => 'page1',
            'pages' => [
                'page1_1' => [
                    'label'      => 'Page 1.1',
                    'uri'        => 'page1/page1_1',
                    'textdomain' => 'LaminasTest_1',
                ],
            ],
        ],
        'page2' => [
            'label'      => 'Page 2',
            'uri'        => 'page2',
            'textdomain' => 'LaminasTest_1',
            'pages'      => [
                'page2_1' => [
                    'label' => 'Page 2.1',
                    'uri'   => 'page2/page2_1',
                ],
                'page2_2' => [
                    'label' => 'Page 2.2',
                    'uri'   => 'page2/page2_2',
                    'pages' => [
                        'page2_2_1' => [
                            'label' => 'Page 2.2.1',
                            'uri'   => 'page2/page2_2/page2_2_1',
                        ],
                        'page2_2_2' => [
                            'label'  => 'Page 2.2.2',
                            'uri'    => 'page2/page2_2/page2_2_2',
                            'active' => '1',
                        ],
                    ],
                ],
                'page2_3' => [
                    'label'      => 'Page 2.3',
                    'uri'        => 'page2/page2_3',
                    'textdomain' => 'LaminasTest_No',
                    'pages'      => [
                        'page2_3_1' => [
                            'label' => 'Page 2.3.1',
                            'uri'   => 'page2/page2_3/page2_3_1',
                        ],
                        'page2_3_2' => [
                            'label'   => 'Page 2.3.2',
                            'uri'     => 'page2/page2_3/page2_3_2',
                            'visible' => '0',
                            'pages'   => [
                                'page2_3_2_1' => [
                                    'label'  => 'Page 2.3.2.1',
                                    'uri'    => 'page2/page2_3/page2_3_2/1',
                                    'active' => '1',
                                ],
                                'page2_3_2_2' => [
                                    'label'  => 'Page 2.3.2.2',
                                    'uri'    => 'page2/page2_3/page2_3_2/2',
                                    'active' => '1',
                                    'pages'  => [
                                        'page_2_3_2_2_1' => [
                                            'label'  => 'Ignore',
                                            'uri'    => '#',
                                            'active' => '1',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'page2_3_3' => [
                            'label'      => 'Page 2.3.3',
                            'uri'        => 'page2/page2_3/page2_3_3',
                            'resource'   => 'admin_foo',
                            'textdomain' => 'LaminasTest_1',
                            'pages'      => [
                                'page2_3_3_1' => [
                                    'label'      => 'Page 2.3.3.1',
                                    'uri'        => 'page2/page2_3/page2_3_3/1',
                                    'active'     => '1',
                                    'textdomain' => 'LaminasTest_2',
                                ],
                                'page2_3_3_2' => [
                                    'label'    => 'Page 2.3.3.2',
                                    'uri'      => 'page2/page2_3/page2_3_3/2',
                                    'resource' => 'guest_foo',
                                    'active'   => '1',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
];
