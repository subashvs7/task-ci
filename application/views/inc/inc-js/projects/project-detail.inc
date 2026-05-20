<script>
jQuery(function ($) {

    var roleColors = {admin:'#c0392b', manager:'#e67e22', member:'#2980b9', viewer:'#7f8c8d'};
    var roleLabels = {admin:'Admin', manager:'Manager', member:'Member', viewer:'Viewer'};

    // ── Add Member ────────────────────────────────────────────────────
    $('#btnAddMember').on('click', function () {
        var userId     = $('#add_member_user').val();
        var role       = $('#add_member_role').val();
        var projectId  = $(this).data('project-id');

        if (!userId) { alert('Please select a user.'); return; }

        var $btn = $(this).prop('disabled', true).text('Adding…');

        $.ajax({
            url: BASE_URL + 'add-project-member',
            type: 'POST',
            dataType: 'json',
            data: { project_id: projectId, user_id: userId, project_role: role },
            success: function (res) {
                if (!res.success) { alert(res.message); return; }

                var name  = res.name;
                var init  = name.split(' ').slice(0, 2).map(function(w){ return w[0].toUpperCase(); }).join('');
                var rc    = roleColors[res.role] || '#95a5a6';
                var pr    = roleLabels[res.project_role] || res.project_role;

                var card = '<div class="member-card" id="mcard-' + res.member_id + '" '
                    + 'style="display:flex;align-items:center;gap:8px;background:#f8f9fa;border-radius:8px;'
                    + 'padding:8px 14px;border:1px solid #dee2e6;min-width:180px;">'
                    + '<div style="width:36px;height:36px;border-radius:50%;background:' + rc + ';display:flex;'
                    + 'align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:13px;flex-shrink:0;">' + init + '</div>'
                    + '<div style="flex:1;">'
                    + '<div style="font-weight:600;font-size:13px;">' + $('<div>').text(name).html() + '</div>'
                    + '<span class="label" style="background:' + rc + ';font-size:10px;">' + pr + '</span>'
                    + '</div>'
                    + '<button class="btn btn-xs btn-default btn-remove-member" data-member-id="' + res.member_id + '" '
                    + 'title="Remove from project" style="opacity:.6;"><i class="fa fa-times"></i></button>'
                    + '</div>';

                $('#membersList').append(card);

                // Remove from dropdown
                $('#add_member_user option[value="' + res.user_id + '"]').remove();
                if ($('#add_member_user option').length <= 1) {
                    $('#addMemberModal').modal('hide');
                    $('[data-target="#addMemberModal"]').hide();
                }
                $('#add_member_user').val('');
                $('#addMemberModal').modal('hide');

                // Update badge count
                var badge = $('.box-warning .badge');
                badge.text(parseInt(badge.text()) + 1);
            },
            error: function () { alert('Server error. Please try again.'); },
            complete: function () { $btn.prop('disabled', false).html('<i class="fa fa-user-plus"></i> Add Member'); }
        });
    });

    // ── Remove Member ─────────────────────────────────────────────────
    $(document).on('click', '.btn-remove-member', function () {
        if (!confirm('Remove this member from the project?')) return;

        var memberId = $(this).data('member-id');
        var $card    = $('#mcard-' + memberId);

        $.ajax({
            url: BASE_URL + 'remove-project-member',
            type: 'POST',
            dataType: 'json',
            data: { member_id: memberId },
            success: function (res) {
                if (!res.success) { alert(res.message); return; }
                $card.fadeOut(300, function () { $(this).remove(); });
                var badge = $('.box-warning .badge');
                badge.text(Math.max(1, parseInt(badge.text()) - 1));
            },
            error: function () { alert('Server error. Please try again.'); }
        });
    });

});
</script>
