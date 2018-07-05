		<div class="navbar-inner">
			<a id="logo" href="/">AccessLocator</a>
			<ul class="nav pull-right">
				<li><a class="nav-home" href="/">Home</a></li>
				<li><a rel="nofollow" class="nav-profile" href="/profile">Profile</a></li>
				<li><a class="nav-faq" href="/faq">FAQ</a></li>
				<li><a class="nav-contact" href="/contact">Contact</a></li>
				@if (Auth::check())
				<li><a class="sign-out" href="/signout" title="Sign out"><i class="fa fa-sign-out"></i><span class="sr-only">Sign out</span></a></li>
				@endif
			</ul>
		</div>
