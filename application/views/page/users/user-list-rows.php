<?php if (empty($record_list)): ?>
<tr><td colspan="6" class="text-center text-muted" style="padding:30px;">No users found.</td></tr>
<?php else: ?>
<?php foreach ($record_list as $j => $u):
  $initials = strtoupper(implode('', array_map(function($w){ return isset($w[0]) ? $w[0] : ''; }, array_slice(explode(' ', $u['name']), 0, 2))));
  $avatarClass = 'ua-' . ($u['role'] ?: 'default');
?>
<tr>
  <td><?php echo $sno + $j + 1; ?></td>
  <td>
    <div style="display:flex; align-items:center; gap:10px;">
      <div class="user-avatar <?php echo $avatarClass; ?>"><?php echo htmlspecialchars($initials); ?></div>
      <div>
        <strong><?php echo htmlspecialchars($u['name']); ?></strong>
        <?php if ($this->session->userdata(SESS_HEAD.'_user_id') == $u['user_id']): ?>
          <span class="label label-primary" style="font-size:9px;">You</span>
        <?php endif; ?>
        <br><small class="text-muted"><?php echo htmlspecialchars($u['email']); ?></small>
      </div>
    </div>
  </td>
  <td>
    <?php $rc = array('admin'=>'danger','manager'=>'warning','team_leader'=>'info','staff'=>'default'); $rl = USER_ROLE_OPT; ?>
    <span class="label label-<?php echo isset($rc[$u['role']])?$rc[$u['role']]:'default'; ?>">
      <?php echo isset($rl[$u['role']]) ? $rl[$u['role']] : $u['role']; ?>
    </span>
  </td>

  <td>
    <span class="label label-<?php echo ($u['status']=='Active')?'success':'danger'; ?>">
      <i class="fa fa-<?php echo ($u['status']=='Active')?'check':'ban'; ?>"></i> <?php echo $u['status']; ?>
    </span>
  </td>
  <td style="font-size:12px;"><?php echo date('d-M-Y', strtotime($u['created_date'])); ?></td>
  <td style="white-space:nowrap;">
    <button class="btn btn-xs btn-warning btn-edit-user"
      data-id="<?php echo $u['user_id']; ?>"
      data-name="<?php echo htmlspecialchars($u['name'], ENT_QUOTES); ?>"
      data-email="<?php echo htmlspecialchars($u['email'], ENT_QUOTES); ?>"
      data-role="<?php echo $u['role']; ?>"
      title="Edit"><i class="fa fa-pencil"></i>
    </button>
    <form method="post" action="<?php echo site_url($s_url . '/' . $sno) ?>" style="display:inline;">
      <input type="hidden" name="mode" value="ToggleStatus">
      <input type="hidden" name="user_id" value="<?php echo $u['user_id']; ?>">
      <?php if ($this->session->userdata(SESS_HEAD.'_user_id') != $u['user_id']): ?>
      <button type="submit" class="btn btn-xs <?php echo ($u['status']=='Active')?'btn-default':'btn-success'; ?>"
        title="<?php echo ($u['status']=='Active')?'Deactivate':'Activate'; ?>"
        onclick="return confirm('<?php echo ($u['status']=='Active')?'Deactivate':'Activate'; ?> <?php echo htmlspecialchars($u['name'], ENT_QUOTES); ?>?')">
        <i class="fa fa-<?php echo ($u['status']=='Active')?'ban':'check'; ?>"></i>
      </button>
      <?php endif; ?>
    </form>
  </td>
</tr>
<?php endforeach; ?>
<?php endif; ?>
