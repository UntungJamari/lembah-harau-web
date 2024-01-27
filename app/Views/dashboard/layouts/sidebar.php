<?php
$uri = service('uri')->getSegments();
$uri1 = $uri[1] ?? 'index';
$uri2 = $uri[2] ?? '';
$uri3 = $uri[3] ?? '';
?>

<div id="sidebar" class="active">
    <div class="sidebar-wrapper active">
        <!-- Sidebar Header -->
        <?= $this->include('web/layouts/sidebar_header'); ?>

        <!-- Sidebar -->
        <div class="sidebar-menu">
            <div class="d-flex flex-column">
                <div class="d-flex justify-content-center avatar avatar-xl me-3" id="avatar-sidebar">
                    <img src="<?= base_url('images/logo.png'); ?>" alt="" srcset="" style="object-fit: cover; max-height: 90px; max-width: 90px;">
                </div>
                <?php if (logged_in()) : ?>
                    <div class="p-2 text-center">
                        <?php if (!empty(user()->first_name)) : ?>
                            Hello, <span class="fw-bold"><?= user()->first_name; ?><?= (!empty(user()->last_name)) ? ' ' . user()->last_name : ''; ?></span> <br> <span class="text-muted mb-0">@<?= user()->username; ?></span>
                        <?php else : ?>
                            Hello, <span class="fw-bold">@<?= user()->username; ?></span>
                        <?php endif; ?>
                        <?php if (in_groups(['owner'])) : ?>
                            <br><span id="homestayName"></span>
                            <script>
                                getHSName('<?= user()->id; ?>');
                            </script>
                        <?php endif; ?>
                    </div>
                <?php else : ?>
                    <div class="p-2 d-flex justify-content-center">Hello, Visitor</div>
                <?php endif; ?>
                <ul class="menu">
                    <li class="sidebar-item">
                        <a href="<?= base_url('web'); ?>" class="sidebar-link">
                            <i class="fa-solid fa-house"></i><span> Home</span>
                        </a>
                    </li>

                    <!-- Manage EV -->
                    <?php if (in_groups(['admin'])) : ?>
                        <li class="sidebar-item <?= ($uri1 == 'event') ? 'active' : '' ?>">
                            <a href="<?= base_url('dashboard/event'); ?>" class="sidebar-link">
                                <i class="fa-solid fa-bullhorn"></i><span> Event</span>
                            </a>
                        </li>
                    <?php endif; ?>

                    <!-- Manage Homestay -->
                    <?php if (in_groups(['owner'])) : ?>
                        <li class="sidebar-item <?= ($uri1 == 'homestay') ? 'active' : '' ?>">
                            <a href="<?= base_url('dashboard/homestay'); ?>" class="sidebar-link">
                                <i class="fa-solid fa-bed"></i><span> Homestay</span>
                            </a>
                        </li>
                        <li class="sidebar-item <?= ($uri1 == 'homestayUnit') ? 'active' : '' ?>">
                            <a href="<?= base_url('dashboard/homestayUnit'); ?>" class="sidebar-link">
                                <i class="fa-solid fa-door-open"></i><span> Unit</span>
                            </a>
                        </li>
                        <li class="sidebar-item <?= ($uri1 == 'tourismPackage') ? 'active' : '' ?>">
                            <a href="<?= base_url('dashboard/tourismPackage'); ?>" class="sidebar-link">
                                <i class="fa-solid fa-box-open"></i><span> Tourism Package</span>
                            </a>
                        </li>
                        <li class="sidebar-item <?= ($uri1 == 'exclusiveActivity') ? 'active' : '' ?>">
                            <a href="<?= base_url('dashboard/exclusiveActivity'); ?>" class="sidebar-link">
                                <i class="fa-solid fa-list-ol"></i><span> Exclusive Activity</span>
                            </a>
                        </li>
                        <li class="sidebar-item <?= ($uri1 == 'reservation') ? 'active' : '' ?>">
                            <a href="<?= base_url('dashboard/reservation'); ?>" class="sidebar-link">
                                <i class="fa-solid fa-book"></i><span> Reservation</span>
                            </a>
                        </li>
                    <?php endif; ?>

                    <!-- Manage Homestay -->
                    <?php if (in_groups(['admin'])) : ?>
                        <li class="sidebar-item <?= ($uri1 == 'homestay') ? 'active' : '' ?> has-sub">
                            <a href="<?= base_url('dashboard/homestay/manage'); ?>" class="sidebar-link">
                                <i class="fa-solid fa-bed"></i><span> Homestay</span>
                            </a>
                            <ul class="submenu <?= ($uri1 == 'homestay') ? 'active' : '' ?><?= ($uri1 == 'facilityHomestay') ? 'active' : '' ?><?= ($uri1 == 'facilityUnit') ? 'active' : '' ?>">
                                <!-- Manage Homestay -->
                                <li class="submenu-item" id="mg-sp">
                                    <a href="<?= base_url('dashboard/homestay/manage'); ?>"><i class="fa-solid fa-list me-3"></i>List</a>
                                </li>
                                <!-- Manage Homestay Facility -->
                                <li class="submenu-item" id="mg-p">
                                    <a href="<?= base_url('dashboard/facilityHomestay'); ?>"><i class="fa-solid fa-hand-holding me-3"></i>Homestay Facility</a>
                                </li>
                                <!-- Manage Homestay Unit Facility -->
                                <li class="submenu-item" id="mg-p">
                                    <a href="<?= base_url('dashboard/facilityUnit'); ?>"><i class="fa-solid fa-hand-holding me-3"></i>Unit Facility</a>
                                </li>
                            </ul>
                        </li>
                    <?php endif; ?>

                    <!-- Manage Attraction -->
                    <?php if (in_groups(['admin'])) : ?>
                        <li class="sidebar-item <?= ($uri1 == 'attraction') ? 'active' : '' ?> has-sub">
                            <a href="" class="sidebar-link">
                                <i class="fa-solid fa-landmark"></i><span> Attraction</span>
                            </a>
                            <ul class="submenu <?= ($uri1 == 'attraction') ? 'active' : '' ?>">
                                <!-- Manage Attraction -->
                                <li class="submenu-item" id="mg-sp">
                                    <a href="<?= base_url('dashboard/attraction'); ?>"><i class="fa-solid fa-list me-3"></i>List</a>
                                </li>
                                <!-- Manage Facility -->
                                <li class="submenu-item" id="mg-p">
                                    <a href="<?= base_url('dashboard/attraction/facility'); ?>"><i class="fa-solid fa-hand-holding me-3"></i>Facility</a>
                                </li>
                            </ul>
                        </li>
                    <?php endif; ?>
                    <!-- Manage Service Provider -->
                    <?php if (in_groups(['admin'])) : ?>
                        <li class="sidebar-item <?= ($uri1 == 'serviceProvider') ? 'active' : '' ?>">
                            <a href="<?= base_url('dashboard/serviceProvider'); ?>" class="sidebar-link">
                                <i class="fa-solid fa-briefcase"></i><span> Service Provider</span>
                            </a>
                        </li>
                    <?php endif; ?>

                    <!-- Manage Souvenir Place -->
                    <?php if (in_groups(['admin'])) : ?>
                        <li class="sidebar-item <?= ($uri1 == 'culinarysouvenirPlace') ? 'active' : '' ?> has-sub">
                            <a href="" class="sidebar-link">
                                <i class="fa-solid fa-bag-shopping"></i><span> Souvenir Place</span>
                            </a>
                            <ul class="submenu <?= ($uri1 == 'souvenirPlace') ? 'active' : '' ?>">
                                <!-- Manage Souvenir Place -->
                                <li class="submenu-item" id="mg-sp">
                                    <a href="<?= base_url('dashboard/souvenirPlace'); ?>"><i class="fa-solid fa-list me-3"></i>List</a>
                                </li>
                                <!-- Manage Product -->
                                <li class="submenu-item" id="mg-p">
                                    <a href="<?= base_url('dashboard/souvenirPlace/product'); ?>"><i class="fa-solid fa-tag me-3"></i>Product</a>
                                </li>
                            </ul>
                        </li>
                    <?php endif; ?>

                    <!-- Manage Culinary Place -->
                    <?php if (in_groups(['admin'])) : ?>
                        <li class="sidebar-item <?= ($uri1 == 'culinaryPlace') ? 'active' : '' ?> has-sub">
                            <a href="" class="sidebar-link">
                                <i class="fa-solid fa-mortar-pestle"></i><span> Culinary Place</span>
                            </a>
                            <ul class="submenu <?= ($uri1 == 'culinaryPlace') ? 'active' : '' ?>">
                                <!-- Manage Souvenir Place -->
                                <li class="submenu-item" id="mg-sp">
                                    <a href="<?= base_url('dashboard/culinaryPlace'); ?>"><i class="fa-solid fa-list me-3"></i>List</a>
                                </li>
                                <!-- Manage Product -->
                                <li class="submenu-item" id="mg-p">
                                    <a href="<?= base_url('dashboard/culinaryPlace/product'); ?>"><i class="fa-solid fa-tag me-3"></i>Product</a>
                                </li>
                            </ul>
                        </li>
                    <?php endif; ?>

                    <!-- Manage Worship Place -->
                    <?php if (in_groups(['admin'])) : ?>
                        <li class="sidebar-item <?= ($uri1 == 'worshipPlace') ? 'active' : '' ?>">
                            <a href="<?= base_url('dashboard/worshipPlace'); ?>" class="sidebar-link">
                                <i class="fa-solid fa-place-of-worship"></i><span> Worship Place</span>
                            </a>
                        </li>
                    <?php endif; ?>

                    <!-- Manage Facility -->
                    <?php if (in_groups(['admin'])) : ?>
                        <li class="sidebar-item <?= ($uri1 == 'facility') ? 'active' : '' ?>">
                            <a href="<?= base_url('dashboard/facility'); ?>" class="sidebar-link">
                                <i class="fa-solid fa-house-circle-check"></i><span> Facility</span>
                            </a>
                        </li>
                    <?php endif; ?>

                    <!-- Manage Recommendation -->
                    <?php if (in_groups(['admin'])) : ?>
                        <li class="sidebar-item <?= ($uri1 == 'recommendation') ? 'active' : '' ?>">
                            <a href="<?= base_url('dashboard/recommendation'); ?>" class="sidebar-link">
                                <i class="fa-solid fa-star"></i><span> Recommendation</span>
                            </a>
                        </li>
                    <?php endif; ?>

                    <!-- Manage Users -->
                    <?php if (in_groups(['admin'])) : ?>
                        <li class="sidebar-item <?= ($uri1 == 'users') ? 'active' : '' ?>">
                            <a href="<?= base_url('dashboard/users'); ?>" class="sidebar-link">
                                <i class="fa-solid fa-users"></i><span> Users</span>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>

        </div>
    </div>
</div>