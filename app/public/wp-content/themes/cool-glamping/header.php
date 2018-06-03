<!DOCTYPE html>
<html lang="en">
<head>
  <?php wp_head(); ?>
</head>
<body>
  <header class="site-header">
    <div class="container">
      <div class="site-header__logo-bar">
        <div class="site-header__logo">
          <a href="#"><img src="<?php echo get_theme_file_uri('/img/cool_logo.png') ?>" alt="Cool Under Canvas Logo"></a>
        </div>
        <div>
          <i class="site-header__menu-trigger fa fa-bars" aria-hidden="true"></i>
        </div>
      </div>
      <div class="site-header__menu">
        <nav class="main-navigation t-center">
          <ul>
            <li class="dropdown">

            </li>
            <li class="main-navigation__dropdown"><a>Accomodation <i class="fas fa-angle-double-down main-navigation__dropdown-icon"></i></a>
              <ul>
                <li><a href="#">- Our Yurts</a></li>
                <li><a href="#">- Our Bell Tents</a></li>
              </ul>
            </li>
            <li><a href="#">Explore</a></li>
            <li><a href="#">Bookings</a></li>
            <li><a href="#">About Us</a></li>
            <li><a href="#">Gallery</a></li>
            <li><a href="#">Blog</a></li>
            <li><a href="#">Contact</a></li>
          </ul>
        </nav>
      </div>
    </div>
  </header>
