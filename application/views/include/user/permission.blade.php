<h1>Manage User Permissions</h1>

<?php echo form_open(); ?>

<table class="table">
    <thead>
        <tr>
            <th>Permission</th>
            <th>Allow</th>
            <th>Deny</th>
            <th>Inherited From Group</th>
        </tr>
    </thead>
    <tbody>
        <?php if($permissions) : ?>
        <?php foreach($permissions as $k => $v) : ?>
        <tr>
            <td><?php echo $v['name']; ?></td>
            <td><?php echo form_radio("perm_{$v['id']}", '1', set_radio("perm_{$v['id']}", '1', $ion_auth_acl->is_allowed($v['key'], $users_permissions))); ?>
            </td>
            <td><?php echo form_radio("perm_{$v['id']}", '0', set_radio("perm_{$v['id']}", '0', $ion_auth_acl->is_denied($v['key'], $users_permissions))) ?>
            </td>
            <td><?php echo form_radio("perm_{$v['id']}", 'X', set_radio("perm_{$v['id']}", 'X', ( $ion_auth_acl->is_inherited($v['key'], $users_permissions) || ! array_key_exists($v['key'], $users_permissions)) ? TRUE : FALSE)); ?>
                (Inherit
                <?php echo ($ion_auth_acl->is_inherited($v['key'], $group_permissions, 'value')) ? "Allow" : "Deny"; ?>)
            </td>
        </tr>
        <?php endforeach; ?>
        <?php else: ?>
        <tr>
            <td colspan="4">There are currently no permissions to manage, please add some permissions</td>
        </tr>
        <?php endif; ?>
    </tbody>
</table>

<p>
    <?php echo form_submit('save', 'Save'); ?>
    <?php echo form_submit('cancel', 'Cancel'); ?>
</p>

<?php echo form_close(); ?>