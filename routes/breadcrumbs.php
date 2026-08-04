<?php

use Diglactic\Breadcrumbs\Breadcrumbs;
use Diglactic\Breadcrumbs\Generator as BreadcrumbTrail;

Breadcrumbs::for('submissions', function (BreadcrumbTrail $trail) {
    $trail->push('Naskah Saya', route('submissions.index'));
});

Breadcrumbs::for('submissions.create', function (BreadcrumbTrail $trail) {
    $trail->parent('submissions');
    $trail->push('Pengajuan Naskah Baru (Step 1)', route('submissions.create'));
});
