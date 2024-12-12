<div class="space-y-6">
    <div class="grid grid-cols-2 gap-6">
        {{-- Tổng doanh thu --}}
        <div class="bg-white rounded-lg p-6 shadow-lg transition duration-300 hover:shadow-xl">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm font-medium text-gray-500">Tổng doanh thu</div>
                    <div class="mt-2 text-2xl font-bold text-primary-600">
                        {{ number_format($stats['total_revenue'], 0) }} VNĐ
                    </div>
                </div>
                <div class="p-3 bg-primary-100 rounded-full">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <div class="mt-4 text-sm text-gray-500">
                Doanh thu từ các dịch vụ đã thực hiện
            </div>
        </div>

        {{-- Tổng công việc --}}
        <div class="bg-white rounded-lg p-6 shadow-lg transition duration-300 hover:shadow-xl">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm font-medium text-gray-500">Tổng công việc</div>
                    <div class="mt-2 text-2xl font-bold text-blue-600">
                        {{ $stats['total_tasks'] }}
                    </div>
                </div>
                <div class="p-3 bg-blue-100 rounded-full">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
            </div>
            <div class="mt-4 text-sm text-gray-500">
                Số lượng công việc được giao
            </div>
        </div>

        {{-- Hoàn thành --}}
        <div class="bg-white rounded-lg p-6 shadow-lg transition duration-300 hover:shadow-xl">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm font-medium text-gray-500">Đã hoàn thành</div>
                    <div class="mt-2 text-2xl font-bold text-green-600">
                        {{ $stats['completed_tasks'] }}
                    </div>
                </div>
                <div class="p-3 bg-green-100 rounded-full">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <div class="mt-4 flex items-center">
                <div class="text-sm text-gray-500">Tỷ lệ hoàn thành:</div>
                <div class="ml-2 text-sm font-medium text-green-600">
                    {{ $stats['total_tasks'] ? round(($stats['completed_tasks'] / $stats['total_tasks']) * 100) : 0 }}%
                </div>
            </div>
        </div>

        {{-- Đang thực hiện --}}
        <div class="bg-white rounded-lg p-6 shadow-lg transition duration-300 hover:shadow-xl">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm font-medium text-gray-500">Đang thực hiện</div>
                    <div class="mt-2 text-2xl font-bold text-yellow-600">
                        {{ $stats['pending_tasks'] }}
                    </div>
                </div>
                <div class="p-3 bg-yellow-100 rounded-full">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <div class="mt-4 flex items-center">
                <div class="text-sm text-gray-500">Cần xử lý:</div>
                <div class="ml-2 text-sm font-medium text-yellow-600">
                    {{ $stats['total_tasks'] ? round(($stats['pending_tasks'] / $stats['total_tasks']) * 100) : 0 }}%
                </div>
            </div>
        </div>
    </div>
</div>
