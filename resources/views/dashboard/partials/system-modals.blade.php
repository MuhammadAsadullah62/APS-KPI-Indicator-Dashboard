<x-directory.view-modal
    id="overviewUserModal"
    title="User profile"
    avatar-id="overviewUserAvatar"
    initials-id="overviewUserInitials"
    :fields="[
        ['label' => 'Full name', 'id' => 'overviewUserName'],
        ['label' => 'Employee ID', 'id' => 'overviewUserEmp'],
        ['label' => 'Email', 'id' => 'overviewUserEmail', 'full' => true, 'email' => true],
        ['label' => 'Role', 'id' => 'overviewUserRole', 'full' => true],
        ['label' => 'Wing & department', 'id' => 'overviewUserWingDept', 'full' => true],
    ]" />
