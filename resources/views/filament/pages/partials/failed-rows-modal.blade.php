<div class="space-y-4">
    @if($failedRows->isEmpty())
        <p class="text-sm text-gray-500 dark:text-gray-400">Tidak ada rincian data gagal yang tercatat.</p>
    @else
        <div class="overflow-x-auto max-h-96 border rounded-lg dark:border-gray-700">
            <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-800 dark:text-gray-400">
                    <tr>
                        <th class="px-4 py-2 border-b">Baris Data</th>
                        <th class="px-4 py-2 border-b">Pesan Error Validasi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($failedRows as $failedRow)
                        <tr class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800">
                            <td class="px-4 py-2 font-mono text-xs whitespace-pre-wrap">
                                {{ is_array($failedRow->data) ? json_encode($failedRow->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : $failedRow->data }}
                            </td>
                            <td class="px-4 py-2 text-red-600 dark:text-red-400 font-semibold">
                                {{ $failedRow->validation_error }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>