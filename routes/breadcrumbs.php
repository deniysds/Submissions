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

Breadcrumbs::for('submissions.create.step3', function (BreadcrumbTrail $trail, $id) {
    $trail->parent('submissions');
    $trail->push('Metadata Naskah & Penulis (Step 3)', route('submissions.create.step3', $id));
});

Breadcrumbs::for('submissions.create.step4', function (BreadcrumbTrail $trail, $id) {
    $trail->parent('submissions');
    $trail->push('Konfirmasi & Finalisasi (Step 4)', route('submissions.create.step4', $id));
});

Breadcrumbs::for('submissions.show', function (BreadcrumbTrail $trail, $submission) {
    $trail->parent('submissions');
    $trail->push('Detail Naskah: ' . ($submission->title ?? 'Submission'), route('submissions.show', $submission->id));
});

// Editor Workflow Breadcrumbs
Breadcrumbs::for('editor.submissions.index', function (BreadcrumbTrail $trail) {
    $trail->push('Penelaahan Redaksi (Editor)', route('editor.submissions.index'));
});

Breadcrumbs::for('editor.reports.index', function (BreadcrumbTrail $trail) {
    $trail->parent('editor.submissions.index');
    $trail->push('Laporan Statistik Kinerja Redaksi', route('editor.reports.index'));
});

Breadcrumbs::for('editor.submissions.show', function (BreadcrumbTrail $trail, $submission) {
    $trail->parent('editor.submissions.index');
    $trail->push('Evaluasi Naskah: ' . ($submission->title ?? 'Editor Review'), route('editor.submissions.show', $submission->id));
});

// Reviewer Workflow Breadcrumbs
Breadcrumbs::for('reviewer.assignments.index', function (BreadcrumbTrail $trail) {
    $trail->push('Tugas Telaah Mitra Bestari', route('reviewer.assignments.index'));
});

Breadcrumbs::for('reviewer.assignments.show', function (BreadcrumbTrail $trail, $assignment) {
    $trail->parent('reviewer.assignments.index');
    $title = $assignment->round?->submission?->title ?? 'Naskah Review';
    $trail->push('Evaluasi Naskah: ' . \Illuminate\Support\Str::limit($title, 40), route('reviewer.assignments.show', $assignment->id));
});
