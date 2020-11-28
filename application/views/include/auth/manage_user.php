<h1>Manage User <strong><?php echo $user->first_name; ?> <?php echo $user->last_name; ?></strong></h1>

<ul>
    <li><?php echo anchor('/admin1/manage', 'Back to admin'); ?></li>
    <li><?php echo anchor('/admin1/users', 'Back to Users'); ?></li>
</ul>

<h3>Users Groups</h3>
<ul>
    <?php foreach($user_groups as $ug) : ?>
    <li><?php echo $ug->description; ?></li>
    <?php endforeach; ?>
</ul>

<h3>Users Permissions (<a href="/admin1/user_permissions/<?php echo $user->id; ?>">Manage User Permissions</a>)</h3>
<ul>
    <?php foreach($user_acl as $acl) : ?>
    <li><?php echo $acl['name']; ?> (<?php if($this->ion_auth_acl->has_permission($acl['key'], $user_acl)) : ?>Allow<?php else: ?>Deny<?php endif; ?><?php if($acl['inherited']) : ?> <strong>Inherited</strong><?php endif; ?>)</li>
    <?php endforeach; ?>
</ul>