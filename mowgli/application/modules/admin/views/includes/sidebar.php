<!-- <div id="sidebar-label">
    <div class="left_arrow" title="Hide Sidebar">&#x25C0;</div>
    <div class="right_arrow" title="Show Sidebar">&#x25B6;</div>
</div> -->
<div id="sidebar" class="sidebar-nav">
        <!-- <div id="pin-holder" class="pin-holder unpinned"></div>

                <a href="{sidebar:logo_url}" target="_blank"><div class="header">{sidebar:logo}</div></a>
                <div class="welcome-msg">Howdy {admin:user}</div>
        <div class="site-links">
                <a href="{site:root}">Site</a> &bull;
                <a href="{site:root}/admin/dashboard">Dashboard</a> &bull;
                <a href="{site:root}/user/logout">Logout</a>
                <br/><br/>
                <a href="{site:root}/user/change_password">Change password</a>

        </div>
        -->
        <!-- Accordion Menu -->
        <ul id="main-nav" class="menu-{active-module}">

                {sidebar:menu}

                <li class="sub-nav-item"><!-- Add the class "current" to current menu item -->


                        <a href="{sidebar:menu:uri}" class="nav-top-item menu-{module}" title="{sidebar:menu:description}" > <!-- Add the class "no-submenu" to menu items with no sub menu -->
                                <i class="icon-plus-sign" style="padding-right:8px;"></i>
                                {sidebar:menu:name}

                                <!-- Module Icon ( hidden temporarily ) -->
                                <!-- <div class="icon" style="background:url({module:icon}) no-repeat scroll 0 0 transparent;background-position:0 -65px;  "></div>-->
                        </a>

                        <ul class="nav-sub-item-grp" >
                                {sidebar:menu:sub}
                                <li><a class="nav-sub-item" href="{sidebar:menu:sub:uri}" title="{sidebar:menu:sub:description}" >{sidebar:menu:sub:name}</a></li>
                                {/sidebar:menu:sub}
                        </ul>
                </li>

                {/sidebar:menu}

        </ul><!--end Accordion Menu-->

        <!--
        <div class="footer">t
                <p style="color: black;">
                        <br/><br/><br/>
                        execution time : {elapsed_time}<br/>
                        memory used    : {memory_usage}<br/> <?php // echo $this->benchmark->memory_usage(); ?>
                </p>
                <p align="left">Copyright 2012 &copy; <a href="http://encube.co.in" target='_blank'>encube.co.in</a></p>
        </div>-->


</div><!-- end Sidebar -->

<!--

{sidebar:menu}
    {sidebar:menu:module_name}
    {sidebar:menu:sub}
        {sidebar:menu:sub:link}
        {sidebar:menu:sub:name}
    {/sidebar:menu:sub}
{/sidebar:menu}

-->