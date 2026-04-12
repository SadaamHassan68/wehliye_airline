<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/functions.php';

require_roles(['admin']);

$base = base_url();
$user = User::current();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['set_role'])) {
    $id = (int) ($_POST['user_id'] ?? 0);
    $role = (string) ($_POST['role'] ?? '');
    if ($id > 0 && $role !== '') {
        $ok = User::adminSetRole($id, $role);
        flash_set($ok ? 'success' : 'error', $ok ? 'Role updated.' : 'Could not update role.');
    }
    header('Location: ' . $base . '/admin/users.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    $id = (int) ($_POST['user_id'] ?? 0);
    if ($id > 0) {
        $ok = User::adminDelete($id);
        flash_set($ok ? 'success' : 'error', $ok ? 'User deleted.' : 'Cannot delete this user (bookings exist or you cannot delete your own account).');
    }
    header('Location: ' . $base . '/admin/users.php');
    exit;
}

$flash = flash_get();
$users = User::adminList();

$pageTitle = 'Users — Wehliye Admin';
$adminSidebar = true;
$activeNav = 'users';

require dirname(__DIR__) . '/includes/header.php';
?>

<?php if ($flash): ?>
    <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?> border-0 shadow-sm rounded-3"><?= htmlspecialchars($flash['message']) ?></div>
<?php endif; ?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <h1 class="ofbms-page-title h3 mb-0">Users</h1>
    <a href="<?= htmlspecialchars($base) ?>/admin/dashboard.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3">Dashboard</a>
</div>

<div class="ofbms-table-wrap">
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Loyalty</th>
                    <th>Joined</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                    <?php $uid = (int) $u['id']; ?>
                    <tr>
                        <td class="fw-medium"><?= htmlspecialchars($u['full_name']) ?></td>
                        <td class="small"><?= htmlspecialchars($u['email']) ?></td>
                        <td>
                            <form method="post" action="<?= htmlspecialchars($base) ?>/admin/users.php" class="d-flex flex-wrap gap-1 align-items-center">
                                <input type="hidden" name="user_id" value="<?= $uid ?>">
                                <select class="form-select form-select-sm rounded-3" name="role" style="min-width: 7rem;">
                                    <option value="passenger" <?= $u['role'] === 'passenger' ? 'selected' : '' ?>>passenger</option>
                                    <option value="agent" <?= $u['role'] === 'agent' ? 'selected' : '' ?>>agent</option>
                                    <option value="admin" <?= $u['role'] === 'admin' ? 'selected' : '' ?>>admin</option>
                                </select>
                                <button type="submit" name="set_role" value="1" class="btn btn-sm btn-outline-primary rounded-pill">Save</button>
                            </form>
                        </td>
                        <td><?= (int) $u['loyalty_points'] ?></td>
                        <td class="text-nowrap small text-muted"><?= htmlspecialchars((string) $u['created_at']) ?></td>
                        <td class="text-end">
                            <form method="post" class="d-inline" data-confirm="Permanently delete this user? They must have no bookings.">
                                <input type="hidden" name="user_id" value="<?= $uid ?>">
                                <button type="submit" name="delete_user" value="1" class="btn btn-sm btn-outline-danger rounded-pill">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
