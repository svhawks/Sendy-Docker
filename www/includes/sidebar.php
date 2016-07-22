<?php $app = isset($_GET['i']) ? $_GET['i'] : get_app_info('restricted_to_app'); ?>

<div class="well sidebar-nav">
    <ul class="nav nav-list">
        <li class="nav-header"><?php echo _('Campaigns');?></li>
        <li <?php if(currentPage()=='app.php'){echo 'class="active"';}?>><a href="<?php echo get_app_info('path').'/app?i='.$app;?>"><i class="icon-home <?php if(currentPage()=='app.php'){echo 'icon-white';}?>"></i> <?php echo _('All campaigns');?></a></li>
        <li <?php if(currentPage()=='create.php' || currentPage()=='send-to.php' || currentPage()=='edit.php'){echo 'class="active"';}?>><a href="<?php echo get_app_info('path').'/create?i='.$app;?>"><i class="icon-edit  <?php if(currentPage()=='create.php' || currentPage()=='send-to.php' || currentPage()=='edit.php'){echo 'icon-white';}?>"></i> <?php echo _('Create new campaign');?></a></li>
    </ul>
    <ul class="nav nav-list">
        <li class="nav-header"><?php echo _('Templates');?></li>
        <li <?php if(currentPage()=='templates.php' || currentPage()=='edit-template.php' || currentPage()=='create-template.php'){echo 'class="active"';}?>><a href="<?php echo get_app_info('path').'/templates?i='.$app;?>"><i class="icon-envelope <?php if(currentPage()=='templates.php' || currentPage()=='edit-template.php' || currentPage()=='create-template.php'){echo 'icon-white';}?>"></i> <?php echo _('All templates');?></a></li>
    </ul>
    <ul class="nav nav-list">
        <li class="nav-header"><?php echo _('Lists & subscribers');?></li>
        <li <?php if(currentPage()=='list.php' || currentPage()=='subscribers.php' || currentPage()=='new-list.php' || currentPage()=='update-list.php' || currentPage()=='delete-from-list.php' || currentPage()=='edit-list.php' || currentPage()=='custom-fields.php' || currentPage()=='autoresponders-list.php' || currentPage()=='autoresponders-create.php' || currentPage()=='autoresponders-emails.php' || currentPage()=='autoresponders-edit.php' || currentPage()=='autoresponders-report.php' || currentPage()=='search-all-lists.php'){echo 'class="active"';}?>><a href="<?php echo get_app_info('path').'/list?i='.$app;?>"><i class="icon-align-justify  <?php if(currentPage()=='list.php' || currentPage()=='subscribers.php' || currentPage()=='new-list.php' || currentPage()=='update-list.php' || currentPage()=='delete-from-list.php' || currentPage()=='edit-list.php' || currentPage()=='custom-fields.php' || currentPage()=='autoresponders-list.php' || currentPage()=='autoresponders-create.php' || currentPage()=='autoresponders-emails.php' || currentPage()=='autoresponders-edit.php' || currentPage()=='autoresponders-report.php' || currentPage()=='search-all-lists.php'){echo 'icon-white';}?>"></i> <?php echo _('View all lists');?></a></li>
    </ul>
    <ul class="nav nav-list">
        <li class="nav-header"><?php echo _('Reports');?></li>
        <li <?php if(currentPage()=='report.php' || currentPage()=='reports.php'){echo 'class="active"';}?>><a href="<?php echo get_app_info('path').'/reports?i='.$app;?>"><i class="icon-zoom-in  <?php if(currentPage()=='report.php' || currentPage()=='reports.php'){echo 'icon-white';}?>"></i> <?php echo _('See reports');?></a></li>
    </ul>
</div>