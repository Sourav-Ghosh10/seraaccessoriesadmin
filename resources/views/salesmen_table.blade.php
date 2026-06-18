            <tbody>
                @foreach($salesmen as $salesman)
                <tr>
                    <td><code>{{ strtoupper($salesman->ref_code) }}</code></td>
                    <td>{{ $salesman->name }}</td>
                    <td>{{ $salesman->email }}</td>
                    <td>{{ $salesman->mobile }}</td>
                    <td style="color: var(--secondary); font-weight: 600;">{{ number_format($salesman->points_balance) }}</td>
                    <td><span class="badge badge-success">{{ $salesman->status }}</span></td>
                    <td>
                        <div class="action-menu-container">
                            <button class="action-btn" onclick="toggleActionMenu(this, event)">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <div class="action-dropdown">
                                <button type="button" onclick="openEditSalesmanModal('{{ $salesman->id }}', '{{ addslashes($salesman->name) }}', '{{ addslashes($salesman->mobile) }}', '{{ addslashes($salesman->email) }}', '{{ $salesman->ref_code }}', '{{ $salesman->status }}', '{{ $salesman->monthly_target }}')">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button type="button" onclick="openEditPointsModal('{{ $salesman->id }}', '{{ $salesman->points_balance }}')">
                                    <i class="fas fa-star" style="color: #f59e0b;"></i> Edit Points
                                </button>
                                <button type="button" onclick="openPerformanceModal('{{ $salesman->id }}', '{{ addslashes($salesman->name) }}')">
                                    <i class="fas fa-chart-line"></i> Performance
                                </button>
                            </div>
                        </div>
                    </td>
                </tr>
                @endforeach
                @if($salesmen->isEmpty())
                <tr>
                    <td colspan="7" style="text-align: center; padding: 20px;">No salesmen found.</td>
                </tr>
                @endif
            </tbody>
            <tfoot style="border-top: none;">
                <tr>
                    <td colspan="7" style="padding: 0;">
                        <div id="paginationContainer" style="padding: 20px 0;">
                            {{ $salesmen->appends(request()->query())->links() }}
                        </div>
                    </td>
                </tr>
            </tfoot>
