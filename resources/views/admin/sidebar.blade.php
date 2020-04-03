<div class="column is-3 ">
    <aside class="menu is-hidden-mobile">
        <p class="menu-label">
            General
        </p>
        <ul class="menu-list">
            <li><a :class="url == '' ? 'is-active' :''" href="{{url('admin/')}}">Dashboard</a></li>
            <li><a :class="url == 'supervisor' ? 'is-active' :''" href="{{url('admin/supervisor')}}">Add Supervisor</a>
            </li>
        </ul>
        <p class="menu-label">
            Administration
        </p>
        <ul class="menu-list">
            <li><a href="{{url('admin/downloadbackup')}}" target="_blank">Download Daily database Backup</a></li>
            <li><a href="{{url('admin/downloadyesterdaybackup')}}" target="_blank">Download Yesterday database
                    Backup</a></li>
            <li><a href="{{url('admin/users')}}">Users</a></li>
            <li><a href="{{url('admin/notifications')}}">Notifications</a></li>
        </ul>
    </aside>
</div>
