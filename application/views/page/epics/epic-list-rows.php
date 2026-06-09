<?php
if (!function_exists('format_hours')) {
    function format_hours($decimal_hours) {
        if (!$decimal_hours || $decimal_hours <= 0) return '-';
        $h = floor($decimal_hours);
        $m = round(($decimal_hours - $h) * 60);
        $parts = [];
        if ($h > 0) $parts[] = $h.'h';
        if ($m > 0) $parts[] = $m.'m';
        return empty($parts) ? '0m' : implode(' ', $parts);
    }
}
?>
<?php if (empty($record_list)): ?>
          <tr><td colspan="8" class="text-center text-muted" style="padding:30px;">No epics found.</td></tr>
          <?php else: ?>
          <?php foreach ($record_list as $j => $e): ?>
          <tr>
            <td><?php echo $sno + $j + 1; ?></td>
            <td>
              <span style="display:inline-block; width:12px; height:12px; background:<?php echo htmlspecialchars($e['color'] ?: '#9b59b6'); ?>; border-radius:50%; margin-right:6px;"></span>
              <strong><?php echo htmlspecialchars($e['name']); ?></strong>
              <?php if ($e['description']): ?><br><small class="text-muted"><?php echo htmlspecialchars(mb_substr($e['description'],0,50)); ?></small><?php endif; ?>
            </td>
            <td>
              <?php if ($e['project_id']): ?>
                <a href="#" class="project-link-modal" data-id="<?php echo $e['project_id']; ?>"><?php echo htmlspecialchars($e['project_name']); ?></a>
                <button class="btn btn-xs btn-default btn-view-project-modal" data-id="<?php echo $e['project_id']; ?>" style="padding: 1px 3px; border-radius: 3px; font-size: 9px;" title="Quick View Team & Effort"><i class="fa fa-eye"></i></button>
              <?php else: ?>
                -
              <?php endif; ?>
            </td>
            <td class="text-center">
              <?php if (!empty($e['document']) && $e['document'] !== 'null' && $e['document'] !== '[]'): ?>
                <button type="button" class="btn btn-xs btn-default btn-view-docs" data-docs="<?php echo htmlspecialchars($e['document'], ENT_QUOTES, 'UTF-8'); ?>" data-id="<?php echo $e['epic_id']; ?>" title="View Documents">
                   <i class="fa fa-file-pdf-o text-danger"></i> Docs
                </button>
              <?php else: ?>
                <button type="button" class="btn btn-xs btn-default btn-view-docs" data-docs="[]" data-id="<?php echo $e['epic_id']; ?>" style="border: 1px dashed #7f8c8d; color: #7f8c8d; background: transparent;" title="Add Document">
                  <i class="fa fa-plus"></i> Add
                </button>
              <?php endif; ?>
            </td>
            <td>
              <div style="margin-bottom:5px;">
                <?php $sc=array('open'=>'default','in_progress'=>'success','done'=>'success','closed'=>'danger'); ?>
                <span class="label label-<?php echo isset($sc[$e['status']])?$sc[$e['status']]:'default'; ?>" style="<?php echo $e['status']=='in_progress' ? 'background-color:#10b981 !important;' : ''; ?>">
                  <?php echo $e['status'] === 'in_progress' ? 'Working' : ucfirst(str_replace('_',' ',$e['status'])); ?>
                </span>
              </div>
              <div>
                <span class="badge badge-priority-<?php echo $e['priority']; ?>"><?php $pl=TASK_PRIORITY_OPT; echo isset($pl[$e['priority']])?$pl[$e['priority']]:$e['priority']; ?></span>
              </div>
            </td>
            <td><span class="badge bg-purple"><?php echo format_hours($e['estimated_time'] ? ($e['estimated_time']/60) : 0); ?></span></td>
            <td><?php echo $e['story_count']; ?> stories</td>
            <td>
              <button type="button" class="btn btn-xs btn-warning btn-edit-epic"
                data-id="<?php echo $e['epic_id']; ?>"
                data-project="<?php echo $e['project_id']; ?>"
                data-name="<?php echo htmlspecialchars($e['name'], ENT_QUOTES); ?>"
                data-description="<?php echo htmlspecialchars($e['description'], ENT_QUOTES); ?>"
                data-status="<?php echo $e['status']; ?>"
                data-priority="<?php echo $e['priority']; ?>"
                data-color="<?php echo htmlspecialchars($e['color'], ENT_QUOTES); ?>"
                data-eh="<?php echo $e['estimated_time'] ? floor($e['estimated_time'] / 60) : 0; ?>"
                data-em="<?php echo $e['estimated_time'] ? ($e['estimated_time'] % 60) : 0; ?>">
                <i class="fa fa-pencil"></i>
              </button>
              <button class="btn btn-xs btn-danger del_record" value="<?php echo $e['epic_id']; ?>" data-tbl="tm_epics" data-col="epic_id"><i class="fa fa-trash"></i></button>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php endif; ?>
