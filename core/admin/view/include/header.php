<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Limitless</title>

	<!-- Global stylesheets -->
	<link href="https://fonts.googleapis.com/css?family=Roboto:400,300,100,500,700,900" rel="stylesheet" type="text/css">
	<?php $this->getStyles() ?>
</head>

<body class="sidebar-xs">

	<!-- change password -->

	<div class="gn-shadow position-fixed">
		<div  class="change-popup gn-center gn-popup-style position-fixed">
			<div class="gn-close-btn"><i class="icon-minus3"></i></div>
			<form id="change-form" action="<?=PATH . core\base\settings\Settings::get('routes')['admin']['alias']?>/reset" method="post">
				<h3>Change password</h3>

				<div class="form-element">
					<label for="current-password">Current password</label>
					<input type="password" id="current-password" name="current-password"/>
				</div>

				<div class="form-element">
					<label for="new-password">New password</label>
					<input type="password" id="new-password" name="new-password"/>
				</div>

				<div class="form-element">
					<label for="confirm-password">Confirm password</label>
					<input type="password" id="confirm-password" name="confirm-password"/>
				</div>

				<input type="hidden" name="table" value="<?=$this->table;?>">

				<div class="form-element d-flex justify-content-between align-items-center">
					<span class="message-popup">Success</span>
					<button id="continue-btn" type="submit" class="btn" disabled>Continue</buttin>
				</div>
			</form>
		</div>
	</div>

	<!-- change password -->

	<!-- Navbar -->
	<div class="navbar navbar-expand-md navbar-dark">

		<div class="navbar-brand">
			<a href="<?=PATH?>" class="d-inline-block">
				<img src="<?=PATH.ADMIN_TEMPLATE?>resources/images/logo_light.png" alt="">
			</a>
		</div>

		<div class="d-md-none">
			<button class="navbar-toggler sidebar-mobile-main-toggle" type="button">
				<i class="icon-paragraph-justify3"></i>
			</button>
		</div>
		
		<div class="collapse navbar-collapse justify-content-between" id="navbar-mobile">
			<ul class="navbar-nav">
				<li class="nav-item">
					<a href="#" class="navbar-nav-link sidebar-control sidebar-main-toggle d-none d-md-block" data-popup="tooltip-demo" title="Expand main" data-placement="bottom" data-container="body" data-trigger="hover">
						<i class="icon-paragraph-justify3"></i>
					</a>
				</li>
						
				
			</ul>

			


			<!-- <span class="navbar-text ml-md-3 mr-md-auto">
				<span class="badge bg-success">Online</span>
			</span> -->

			<ul class="navbar-nav">
				
				<li class="nav-item dropdown dropdown-user">
					<a href="#" class="navbar-nav-link dropdown-toggle" data-toggle="dropdown">
						<img src="" class="rounded-circle" alt="">
						<span>Patton</span>
					</a>

					<div class="dropdown-menu dropdown-menu-right">
						<a href="#" class="dropdown-item"><i class="icon-user-plus"></i> My profile</a>
						<a href="#" class="dropdown-item"><i class="icon-coins"></i> My balance</a>
						<a href="#" class="dropdown-item"><i class="icon-comment-discussion"></i> Messages <span class="badge badge-pill bg-blue ml-auto">58</span></a>
						<div class="dropdown-divider"></div>
						<a id="change-pass-btn" class="dropdown-item"><i class="icon-cog5"></i> Change password</a>
						<a href="<?= PATH . core\base\settings\Settings::get('routes')['admin']['alias']?>/login/logout" class="dropdown-item"><i class="icon-switch2"></i> Logout</a>
					</div>
				</li>
			</ul>
		</div>
	</div>
	<!-- Navbar -->

	
	<!-- Page -->
	<div class="page-content">

		<!-- Sidebar -->
		<div class="sidebar sidebar-dark sidebar-main sidebar-expand-md">

			<!-- Sidebar mobile toggler -->
			<div class="sidebar-mobile-toggler text-center">
				<a href="#" class="sidebar-mobile-main-toggle">
					<i class="icon-arrow-left8"></i>
				</a>
				Navigation
				<a href="#" class="sidebar-mobile-expand">
					<i class="icon-screen-full"></i>
					<i class="icon-screen-normal"></i>
				</a>
			</div>
			<!-- /sidebar mobile toggler -->


			<!-- Sidebar content -->
			<div class="sidebar-content">

				<!-- User menu -->
				<div class="sidebar-user">
					<div class="card-body">
						<div class="media">
							<div class="mr-3">
								<a href="#"><img src="<?=PATH . UPLOAD_DIR . $this->userId[0]['image']?>" width="38" height="38" class="rounded-circle" alt=""></a>
							</div>

							<div class="media-body">
								<div class="media-title font-weight-semibold">Victoria Baker</div>
								<div class="font-size-xs opacity-50">
									<i class="icon-pin font-size-sm"></i> &nbsp;Santa Ana, CA
								</div>
							</div>

							<div class="ml-3 align-self-center">
								<a href="#" class="text-white"><i class="icon-cog3"></i></a>
							</div>
						</div>
					</div>
				</div>
				<!-- /user menu -->


				<!-- Main navigation -->
				<div class="card card-sidebar-mobile">
					<ul class="nav nav-sidebar" data-nav-type="accordion">

						<li class="nav-item-header"><div class="text-uppercase font-size-xs line-height-xs">Main</div> <i class="icon-menu" title="Main"></i></li>
						
						

						<?php if($this->menu) :?>
							<?php foreach($this->menu as $table => $value) :?>

								<li class="nav-item nav-item-submenu">
									<a href="<?=$this->admin_path?>show/<?=$table?>" class="nav-link <?php if($table === $this->table) echo "active"; ?>">
										<i class="<?=$value['icon'] ? $value['icon'] : 'icon-home4'?>"></i>
										<span><?=$value['name'] ? $value['name'] : $table?></span>
									</a>

									<ul class="nav nav-group-sub" data-submenu-title="<?=$value['name'] ? $value['name'] : $table?>">
										<li class="nav-item">
										
										</li>
									</ul>
								</li>
																	
								
							<?php endforeach; ?>
						<?php endif;?>


					</ul>
				</div>
				<!-- /main navigation -->

			</div>
			<!-- /sidebar content -->
			
		</div>
		<!-- Sidebar -->


		<!-- Content -->
		<div class="content-wrapper">

			<!-- Header -->
			<div class="page-header page-header-light">
				<div class="page-header-content header-elements-md-inline">
					<div class="page-title d-flex">
						<h4><i class="icon-arrow-left52 mr-2"></i> <span class="font-weight-semibold">Sidebars</span> - Mini Hideable</h4>
						<a href="#" class="header-elements-toggle text-default d-md-none"><i class="icon-more"></i></a>
					</div>			

					<div class="header-elements d-none">

						<form autocomplete="off" class="position-relative gn-form-search mr-4" action="<?=PATH . core\base\settings\Settings::get('routes')['admin']['alias']?>/search">
							<div class="gn-search">
								<div class="gn-search-icon"></div>
								<div class="gn-search-input-div">
									<input type="text" name="search" placeholder="Type to Search..." class="gn-search-input"> 
									<input type="hidden" name="search_table" value="<?=$this->table?>"> 
								</div>
							</div>
							<div class="gn-dropdown">
								
							</div>
						</form>
						
						<!-- <div class="d-flex justify-content-center">
							<a href="" class="btn btn-link btn-float text-default sitemap-button"><i class="icon-lan2"></i> <span>Create Sitemap</span></a>
						</div> -->
						
					</div>
				</div>

				<div class="breadcrumb-line breadcrumb-line-light header-elements-md-inline">
					<div class="d-flex">
						<div class="breadcrumb">

							<a href="<?= PATH . core\base\settings\Settings::get('routes')['admin']['alias'] ?>/show" class="breadcrumb-item"><i class="icon-home2 mr-2"></i> Home</a>
								<?php 
									foreach ($this->parameters as $key => $value){  // ????
									
										if($key == $this->table && empty($value)){
											echo '<span class="breadcrumb-item active">' . $this->menu[$this->table]['name'] . '</span>';
										}
									}
								?>

							<!-- <span class="breadcrumb-item active">Mini hideable</span> -->

						</div>

						<a href="#" class="header-elements-toggle text-default d-md-none"><i class="icon-more"></i></a>
					</div>

					<div class="header-elements d-none">
						<div class="breadcrumb justify-content-center">
							<a href="#" class="breadcrumb-elements-item">
								<i class="icon-comment-discussion mr-2"></i>
								Support
							</a>

							<div class="breadcrumb-elements-item dropdown p-0">
								<a href="#" class="breadcrumb-elements-item dropdown-toggle" data-toggle="dropdown">
									<i class="icon-gear mr-2"></i>
									Map
								</a>

								<div class="dropdown-menu dropdown-menu-right">
									<a href="<?= PATH . core\base\settings\Settings::get('routes')['admin']['alias'] ?>/createsitemap" class="dropdown-item sitemap-button"><i class="icon-lan2"></i> Create sitemap</a>
									<a href="<?= PATH . core\base\settings\Settings::get('routes')['admin']['alias'] ?>/sitemap" class="dropdown-item"><i class="icon-statistics"></i> Page sitemap</a>
									<!-- <a href="#" class="dropdown-item"><i class="icon-accessibility"></i> Accessibility</a>
									<div class="dropdown-divider"></div>
									<a href="#" class="dropdown-item"><i class="icon-gear"></i> All settings</a> -->
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<!-- Header -->
