@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/staff_list.css') }}">
@endsection

@section('content')
<main class="staff">
    <h2 class="staff-ttl">スタッフ一覧</h2>

    <table class="staff-table">
        <thead>
            <tr class="table-row-header">
                <th class="table-name">名前</th>
                <th class="table-mail">メールアドレス</th>
                <th class="table-attendance">月次勤怠</th>
            </tr>
        </thead>
        <tbody>
        @foreach ($users as $user)
            <tr class="table-row">
                <td class="table-cell-name">
                    {{ $user->name }}
                </td>
                <td class="table-cell">
                    {{ $user->email }}
                </td>
                <td class="table-cell">
                    <a href="{{ route('admin_attendance_staff', $user->id) }}" class="table-cell-detail">
                        詳細
                    </a>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</main>
@endsection