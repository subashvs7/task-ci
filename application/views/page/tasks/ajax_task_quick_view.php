<?php
$cur_uid  = (int)$this->session->userdata(SESS_HEAD . '_user_id');
$cur_role = $this->session->userdata(SESS_HEAD . '_role');
$is_active_session = ($task['work_session_status'] === 'active');
$is_my_session     = $is_active_session && ((int)$task['active_session_user'] === $cur_uid);
$is_done_closed    = in_array($task['status'], array('done','closed'));
$can_toggle        = in_array($cur_role, array('staff','admin')) && !$is_done_closed;
$is_mine_task      = ((int)$task['assigned_to'] === $cur_uid);

$logged_h    = (float)$task['total_logged_hours'];
if ($is_active_session && !empty($task['open_session_start'])) {
    $start_time = strtotime($task['open_session_start']);
    $elapsed_min = round((time() - $start_time) / 60);
    $logged_h += ($elapsed_min / 60);
}
$estimated_h = (float)$task['estimated_hours'];
$remaining_h = max(0, $estimated_h - $logged_h);
$is_overdue  = ($estimated_h > 0 && $logged_h > $estimated_h);

$time_progress = 0;
if ($estimated_h > 0) {
    $time_progress = min(100, round(($logged_h / $estimated_h) * 100));
} elseif ($is_done_closed) {
    $time_progress = 100;
}

$is_deadline_overdue = false;
if (!$is_done_closed && !empty($task['due_date']) && strtotime($task['due_date']) < strtotime('today')) {
    $is_deadline_overdue = true;
}
?>

<div style="font-family: 'Inter', 'Helvetica Neue', Helvetica, Arial, sans-serif;">
    <!-- HEADER -->
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 25px;">
        <div>
            <h3 style="margin: 0 0 5px 0; font-weight: 700; color: #1e293b; font-size: 22px;">
                <?php echo htmlspecialchars($task['title']); ?>
                <?php if ($is_deadline_overdue): ?>
                    <span style="display: inline-block; background: #ef4444; color: white; font-size: 10px; font-weight: 600; padding: 2px 8px; border-radius: 12px; vertical-align: middle; margin-left: 8px; letter-spacing: 0.5px;"><i class="fa fa-warning"></i> OVERDUE</span>
                <?php endif; ?>
            </h3>
            <div style="color: #64748b; font-size: 13px;">
                <i class="fa fa-folder" style="color: #cbd5e1;"></i> <strong style="color: #475569;"><?php echo htmlspecialchars($task['project_name'] ?: 'No Project'); ?></strong> 
                <span style="margin: 0 8px; color: #cbd5e1;">|</span>
                <i class="fa fa-user" style="color: #cbd5e1;"></i> <strong style="color: #475569;"><?php echo htmlspecialchars($task['assignee_name'] ?: 'Unassigned'); ?></strong>
            </div>
        </div>
        <div style="text-align: right;">
            <div style="margin-bottom: 6px;">
                <span style="display: inline-block; background: #f1f5f9; color: #475569; font-size: 11px; font-weight: 600; padding: 4px 10px; border-radius: 6px; letter-spacing: 0.5px; text-transform: uppercase;">
                    <i class="fa fa-tag text-muted"></i> <?php $sl=TASK_STATUS_OPT; echo isset($sl[$task['status']])?$sl[$task['status']]:$task['status']; ?>
                </span>
            </div>
            <div>
                <span style="display: inline-block; background: #fff7ed; color: #ea580c; border: 1px solid #fdba74; font-size: 11px; font-weight: 600; padding: 3px 10px; border-radius: 6px; letter-spacing: 0.5px; text-transform: uppercase;">
                    <i class="fa fa-flag"></i> <?php $pl=TASK_PRIORITY_OPT; echo isset($pl[$task['priority']])?$pl[$task['priority']]:$task['priority']; ?>
                </span>
            </div>
        </div>
    </div>

    <!-- WORK SESSION ACTION -->
    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; text-align: center; margin-bottom: 25px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);">
        <h5 style="margin: 0 0 15px 0; font-size: 11px; color: #94a3b8; font-weight: 600; letter-spacing: 1px; text-transform: uppercase;">Work Session Controller</h5>
        
        <?php if ($is_done_closed): ?>
            <div style="display: inline-block; background: #dcfce7; color: #166534; padding: 10px 24px; border-radius: 30px; font-weight: 600; font-size: 15px;">
                <i class="fa fa-check-circle"></i> Task Completed
            </div>
        <?php elseif ($is_my_session || $is_child_my_session): ?>
            <div style="display: flex; justify-content: center; align-items: center; gap: 20px;">
                <div style="text-align: left;">
                    <div style="font-size: 11px; color: #64748b; font-weight: 600; text-transform: uppercase;">Active Session Time</div>
                    <div class="session-timer" data-start-ts="<?php echo strtotime($task['open_session_start']); ?>" data-start="<?php echo htmlspecialchars($task['open_session_start']); ?>" style="font-size: 24px; font-weight: 700; color: #0f172a; font-family: 'Courier New', Courier, monospace; letter-spacing: 1px;">00:00:00</div>
                </div>
                <button class="btn btn-task-session" data-task="<?php echo $task['task_id']; ?>" data-action="stop" style="background: #ef4444; color: white; border: none; padding: 12px 28px; border-radius: 30px; font-weight: 600; font-size: 15px; box-shadow: 0 4px 14px rgba(239, 68, 68, 0.4); transition: all 0.2s;">
                    <i class="fa fa-stop-circle" style="margin-right: 6px;"></i> Stop Work
                </button>
            </div>
            <?php if ($is_child_my_session): ?>
            <div style="text-align:center; font-size:11px; font-weight:600; color:#e67e22; margin-top:8px;">
                <i class="fa fa-info-circle"></i> You are currently working on a sub-task.
            </div>
            <?php endif; ?>
        <?php elseif ($is_active_session || $is_child_active_session): ?>
            <div style="display: inline-block; background: #fff7ed; color: #ea580c; padding: 10px 24px; border-radius: 30px; font-weight: 600; font-size: 15px;">
                <i class="fa fa-circle" style="animation: blinker 1.5s linear infinite; margin-right: 6px;"></i> <?php echo htmlspecialchars($is_child_active_session ? $task['child_worker_name'] : 'Colleague'); ?> is currently working
            </div>
        <?php elseif ($can_toggle && $is_mine_task): ?>
            <button class="btn btn-task-session" data-task="<?php echo $task['task_id']; ?>" data-action="start" style="background: #10b981; color: white; border: none; padding: 12px 32px; border-radius: 30px; font-weight: 600; font-size: 16px; box-shadow: 0 4px 14px rgba(16, 185, 129, 0.4); transition: all 0.2s;">
                <i class="fa fa-play" style="margin-right: 6px;"></i> Start Work
            </button>
        <?php else: ?>
            <div style="display: inline-block; background: #f1f5f9; color: #64748b; padding: 10px 24px; border-radius: 30px; font-weight: 600; font-size: 14px;">
                <i class="fa fa-lock"></i> Not Assignable / Inactive
            </div>
        <?php endif; ?>
    </div>

    <!-- TIME TRACKING WIDGETS -->
    <div style="display: flex; gap: 15px; margin-bottom: 25px;">
        <!-- Estimated -->
        <div style="flex: 1; background: white; border: 1px solid #e2e8f0; border-radius: 12px; padding: 15px; text-align: center; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
            <div style="font-size: 10px; color: #94a3b8; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">Estimated</div>
            <?php if ($estimated_h > 0): ?>
                <div style="font-size: 22px; font-weight: 800; color: #334155;">
                    <?php echo round($estimated_h, 2); ?><span style="font-size: 12px; font-weight: 600; color: #94a3b8; margin-left: 4px;">h</span>
                </div>
                <?php if (!empty($task['epic_estimated_time']) && $task['epic_estimated_time'] > 0): ?>
                    <div style="margin-top: 4px; font-size: 11px; color: #9b59b6; font-weight: 600;">Epic: <?php echo round((float)$task['epic_estimated_time'] / 60, 2); ?>h</div>
                <?php endif; ?>
            <?php elseif (!empty($task['epic_estimated_time']) && $task['epic_estimated_time'] > 0): ?>
                <div style="font-size: 22px; font-weight: 800; color: #9b59b6;">
                    <?php echo round((float)$task['epic_estimated_time'] / 60, 2); ?><span style="font-size: 12px; font-weight: 600; color: #c39bd3; margin-left: 4px;">h (Epic)</span>
                </div>
            <?php else: ?>
                <div style="font-size: 22px; font-weight: 800; color: #334155;">--</div>
            <?php endif; ?>
        </div>

        <!-- Logged -->
        <div style="flex: 1; background: white; border: 1px solid #e2e8f0; border-radius: 12px; padding: 15px; text-align: center; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
            <div style="font-size: 10px; color: #94a3b8; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">Logged</div>
            <div style="font-size: 22px; font-weight: 800; color: #0284c7;">
                <?php echo round($logged_h, 2); ?><span style="font-size: 12px; font-weight: 600; color: #7dd3fc; margin-left: 4px;">h</span>
            </div>
        </div>

        <!-- Remaining -->
        <div style="flex: 1; background: <?php echo $is_overdue ? '#fef2f2' : 'white'; ?>; border: 1px solid <?php echo $is_overdue ? '#fecaca' : '#e2e8f0'; ?>; border-radius: 12px; padding: 15px; text-align: center; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
            <div style="font-size: 10px; color: <?php echo $is_overdue ? '#ef4444' : '#94a3b8'; ?>; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">Remaining</div>
            <?php if ($estimated_h > 0): ?>
                <div style="font-size: 22px; font-weight: 800; color: <?php echo $is_overdue ? '#ef4444' : '#10b981'; ?>;">
                    <?php if ($is_overdue): ?>
                        +<?php echo round($logged_h - $estimated_h, 2); ?><span style="font-size: 12px; font-weight: 600; opacity: 0.7; margin-left: 4px;">h over</span>
                    <?php else: ?>
                        <?php echo round($remaining_h, 2); ?><span style="font-size: 12px; font-weight: 600; opacity: 0.7; margin-left: 4px;">h</span>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div style="font-size: 22px; font-weight: 800; color: #cbd5e1;">--</div>
            <?php endif; ?>
        </div>
    </div>

    <!-- PROGRESS BAR -->
    <div>
        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 8px;">
            <div style="font-size: 11px; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Time Progression</div>
            <div style="font-size: 13px; font-weight: 700; color: <?php echo $is_overdue ? '#ef4444' : '#334155'; ?>;"><?php echo $time_progress; ?>%</div>
        </div>
        <div style="height: 10px; background: #f1f5f9; border-radius: 10px; overflow: hidden; box-shadow: inset 0 1px 2px rgba(0,0,0,0.05);">
            <div style="height: 100%; width: <?php echo $time_progress; ?>%; background: <?php echo $time_progress == 100 && !$is_overdue ? '#10b981' : ($is_overdue ? '#ef4444' : 'linear-gradient(90deg, #38bdf8 0%, #0284c7 100%)'); ?>; border-radius: 10px; transition: width 0.8s cubic-bezier(0.4, 0, 0.2, 1);"></div>
        </div>
    </div>
</div>

<style>
/* Add a hover effect for the modern pill buttons */
.btn-task-session:hover {
    transform: translateY(-2px);
    opacity: 0.95;
}
.btn-task-session:active {
    transform: translateY(0);
}
</style>
