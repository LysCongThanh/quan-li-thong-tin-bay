{{-- resources/views/filament/task-stat.blade.php --}}
<div class="space-y-6">
    {{-- Thông tin công việc --}}
    <div class="bg-white rounded-lg p-4 shadow">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Thông tin công việc</h3>
        <div class="space-y-3">
            <div class="flex justify-between">
                <span class="text-sm font-medium text-gray-500">Tên công việc:</span>
                <span class="text-sm text-gray-900">{{ $record->task->name }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-sm font-medium text-gray-500">Khách hàng:</span>
                <span class="text-sm text-gray-900">{{ $record->task->customer->name }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-sm font-medium text-gray-500">Ngày tạo:</span>
                <span class="text-sm text-gray-900">{{ $record->created_at->format('d/m/Y H:i') }}</span>
            </div>
        </div>
    </div>

    {{-- Thông tin dịch vụ --}}
    <div class="bg-white rounded-lg p-4 shadow">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Chi tiết dịch vụ</h3>
        <div class="space-y-3">
            <div class="flex justify-between">
                <span class="text-sm font-medium text-gray-500">Tên dịch vụ:</span>
                <span class="text-sm text-gray-900">{{ $record->service_name }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-sm font-medium text-gray-500">Số lượng:</span>
                <span class="text-sm text-gray-900">{{ $record->quantity }} {{ $record->service_unit }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-sm font-medium text-gray-500">Đơn giá:</span>
                <span class="text-sm text-gray-900">{{ number_format($record->service_price, 0) }} VNĐ</span>
            </div>
            <div class="flex justify-between">
                <span class="text-sm font-medium text-gray-500">Tổng tiền:</span>
                <span class="text-sm font-semibold text-primary-600">{{ number_format($record->quantity * $record->service_price, 0) }} VNĐ</span>
            </div>
            <div class="flex justify-between">
                <span class="text-sm font-medium text-gray-500">Số tiền nhận:</span>
                <span class="text-sm font-semibold text-success-600">{{ number_format($record->money_received, 0) }} VNĐ</span>
            </div>
        </div>
    </div>

    {{-- Trạng thái --}}
    <div class="bg-white rounded-lg p-4 shadow">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Trạng thái</h3>
        <div class="space-y-3">
            <div class="flex justify-between items-center">
                <span class="text-sm font-medium text-gray-500">Tình trạng:</span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                   {{ match($record->status) {
                       'pending' => 'bg-warning-100 text-warning-800',
                       'in_progress' => 'bg-primary-100 text-primary-800',
                       'completed' => 'bg-success-100 text-success-800',
                       'cancelled' => 'bg-danger-100 text-danger-800',
                       default => 'bg-gray-100 text-gray-800'
                   } }}">
                   {{ match($record->status) {
                       'pending' => 'Chờ xử lý',
                       'in_progress' => 'Đang thực hiện',
                       'completed' => 'Hoàn thành',
                       'cancelled' => 'Đã hủy',
                       default => $record->status
                   } }}
               </span>
            </div>
            @if($record->note)
                <div class="mt-4">
                    <span class="text-sm font-medium text-gray-500">Ghi chú:</span>
                    <p class="mt-1 text-sm text-gray-900">{{ $record->note }}</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Người thực hiện --}}
    <div class="bg-white rounded-lg p-4 shadow">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Người thực hiện</h3>
        <div class="space-y-3">
            <div class="flex justify-between">
                <span class="text-sm font-medium text-gray-500">Phi công:</span>
                <span class="text-sm text-gray-900">{{ $record->task->pilot->name }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-sm font-medium text-gray-500">Nhân viên hỗ trợ:</span>
                <span class="text-sm text-gray-900">{{ $record->task->support->name }}</span>
            </div>
            @if($record->reported_by)
                <div class="flex justify-between">
                    <span class="text-sm font-medium text-gray-500">Người báo cáo:</span>
                    <span class="text-sm text-gray-900">{{ $record->reporter->name }}</span>
                </div>
            @endif
        </div>
    </div>
</div>
