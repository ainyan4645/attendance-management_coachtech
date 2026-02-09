@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/staff/stamp_correction_list.css') }}">
@endsection

@section('content')
<main class="request">
    <h2 class="request-ttl">申請一覧</h2>
    <div class="tab-inner">

        <nav class="tabs">
        <button class="tab active" data-tab="pending">
            承認待ち
        </button>
        <button class="tab" data-tab="approved">
            承認済み
        </button>
        </nav>
    </div>

    <table class="request-table">
        <thead>
            <tr class="table-row-header">
                <th class="table-state">状態</th>
                <th class="table-name">名前</th>
                <th class="table-target-date">対象日時</th>
                <th class="table-reason">申請理由</th>
                <th class="table-request-date">申請日時</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody data-tab="pending">
        @foreach ($pendingRequests as $request)
            <tr class="table-row">
                <td class="table-cell-state">
                    承認待ち
                </td>
                <td class="table-cell">
                    {{ $request->user->name }}
                </td>
                <td>
                    {{ $request->target_date->format('Y/m/d') }}
                </td>
                <td class="table-cell">
                    {{ $request->remark }}
                </td>
                <td>
                    {{ $request->created_at->format('Y/m/d') }}
                </td>
                <td>
                    <form
                        action="{{ route('attendance_detail', ['id' => $request->attendance_id]) }}"
                        method="GET"
                    >
                        <input type="hidden" name="date" value="{{ $request->target_date->toDateString() }}">
                        <button type="submit" class="table-cell-detail">
                            詳細
                        </button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
        <tbody data-tab="approved">
        @foreach ($approvedRequests as $request)
            <tr class="table-row">
                <td class="table-cell-state">
                    承認済み
                </td>
                <td class="table-cell">
                    {{ $request->user->name }}
                </td>
                <td>
                    {{ $request->target_date->format('Y/m/d') }}
                </td>
                <td class="table-cell">
                    {{ $request->remark }}
                </td>
                <td>
                    {{ $request->created_at->format('Y/m/d') }}
                </td>
                <td>
                    <form
                        action="{{ route('attendance_detail', ['id' => $request->attendance_id]) }}"
                        method="GET"
                    >
                        <input type="hidden" name="date" value="{{ $request->target_date->toDateString() }}">
                        <button type="submit" class="table-cell-detail">
                            詳細
                        </button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</main>

<script>
document.querySelectorAll('.tab').forEach(tab => {
    tab.addEventListener('click', () => {
        // タブの active 切り替え
        document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
        tab.classList.add('active');

        const target = tab.dataset.tab;

        document.querySelectorAll('tbody[data-tab]').forEach(tbody => {
            tbody.style.display =
                tbody.dataset.tab === target ? '' : 'none';
        });
    });
});
</script>
@endsection