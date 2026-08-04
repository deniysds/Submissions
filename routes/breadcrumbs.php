<?php

use Diglactic\Breadcrumbs\Breadcrumbs;
use Diglactic\Breadcrumbs\Generator as BreadcrumbTrail;

Breadcrumbs::for('submissions', function (BreadcrumbTrail $trail) {
    $trail->push('Naskah Saya', route('submissions.index'));
});

Breadcrumbs::for('submissions.create.step1', function (BreadcrumbTrail $trail) {
    $trail->parent('submissions');
    $trail->push('Pengajuan Naskah Baru (Step 1)', route('submissions.create.step1'));
});

Breadcrumbs::for('submissions.create.step2', function (BreadcrumbTrail $trail, $id) {
    $trail->parent('submissions');
    $trail->push('Unggah Berkas Naskah (Step 2)', route('submissions.create.step2', $id));
});
