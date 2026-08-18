@extends('layouts.admin')

@section('title', 'New Journal Entry')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.journals.index') }}">Journals</a></li>
<li class="breadcrumb-item active" aria-current="page">New Entry</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <form action="{{ route('admin.journals.store') }}" method="POST" id="journalForm">
            @csrf
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">General Information</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Entry Date</label>
                            <input type="date" name="entry_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-8 mb-3">
                            <label class="form-label">Description / Narrative</label>
                            <input type="text" name="description" class="form-control" placeholder="Purpose of this entry..." required>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Journal Items</h3>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="addRow">
                        <i class="fe fe-plus"></i> Add Row
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="journalTable">
                            <thead>
                                <tr>
                                    <th style="width: 250px;">Account Type</th>
                                    <th style="width: 250px;">Specific Account (Optional)</th>
                                    <th>Debit</th>
                                    <th>Credit</th>
                                    <th>Description</th>
                                    <th style="width: 50px;"></th>
                                </tr>
                            </thead>
                            <tbody id="journalBody">
                                <tr class="item-row">
                                    <td>
                                        <select name="items[0][account_type]" class="form-select account-type" required>
                                            <option value="General">General Ledger</option>
                                            <option value="Supplier">Supplier Account</option>
                                            <option value="Expense">Expense Account</option>
                                        </select>
                                    </td>
                                    <td>
                                        <select name="items[0][account_id]" class="form-select account-select" style="display: none;">
                                            <option value="">-- Select Supplier --</option>
                                            @foreach($suppliers as $supplier)
                                                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                            @endforeach
                                        </select>
                                        <span class="text-muted account-placeholder">N/A</span>
                                    </td>
                                    <td>
                                        <input type="number" name="items[0][debit]" class="form-control debit-input" step="0.01" value="0.00" required>
                                    </td>
                                    <td>
                                        <input type="number" name="items[0][credit]" class="form-control credit-input" step="0.01" value="0.00" required>
                                    </td>
                                    <td>
                                        <input type="text" name="items[0][description]" class="form-control" placeholder="Optional row desc">
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-danger remove-row" style="display: none;">
                                            <i class="fe fe-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr class="item-row">
                                    <td>
                                        <select name="items[1][account_type]" class="form-select account-type" required>
                                            <option value="General">General Ledger</option>
                                            <option value="Supplier">Supplier Account</option>
                                            <option value="Expense">Expense Account</option>
                                        </select>
                                    </td>
                                    <td>
                                        <select name="items[1][account_id]" class="form-select account-select" style="display: none;">
                                            <option value="">-- Select Supplier --</option>
                                            @foreach($suppliers as $supplier)
                                                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                            @endforeach
                                        </select>
                                        <span class="text-muted account-placeholder">N/A</span>
                                    </td>
                                    <td>
                                        <input type="number" name="items[1][debit]" class="form-control debit-input" step="0.01" value="0.00" required>
                                    </td>
                                    <td>
                                        <input type="number" name="items[1][credit]" class="form-control credit-input" step="0.01" value="0.00" required>
                                    </td>
                                    <td>
                                        <input type="text" name="items[1][description]" class="form-control" placeholder="Optional row desc">
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-danger remove-row">
                                            <i class="fe fe-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr class="table-info fw-bold">
                                    <td colspan="2" class="text-end">TOTALS</td>
                                    <td id="totalDebit">0.00</td>
                                    <td id="totalCredit">0.00</td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr id="balanceError" class="table-danger" style="display: none;">
                                    <td colspan="6" class="text-center">Warning: Total debits must equal total credits!</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <a href="{{ route('admin.journals.index') }}" class="btn btn-secondary me-2">Cancel</a>
                    <button type="submit" class="btn btn-primary" id="submitBtn">Post Journal Entry</button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        let rowCount = 2;

        function updateTotals() {
            let debitSum = 0;
            let creditSum = 0;
            
            $('.debit-input').each(function() {
                debitSum += parseFloat($(this).val()) || 0;
            });
            
            $('.credit-input').each(function() {
                creditSum += parseFloat($(this).val()) || 0;
            });
            
            $('#totalDebit').text(debitSum.toFixed(2));
            $('#totalCredit').text(creditSum.toFixed(2));
            
            if (debitSum.toFixed(2) === creditSum.toFixed(2) && debitSum > 0) {
                $('#balanceError').hide();
                $('#submitBtn').prop('disabled', false);
            } else {
                $('#balanceError').show();
                $('#submitBtn').prop('disabled', true);
            }
        }

        $(document).on('change', '.account-type', function() {
            let row = $(this).closest('tr');
            if ($(this).val() === 'Supplier') {
                row.find('.account-select').show();
                row.find('.account-placeholder').hide();
            } else {
                row.find('.account-select').hide().val('');
                row.find('.account-placeholder').show();
            }
        });

        $(document).on('input', '.debit-input, .credit-input', function() {
            let row = $(this).closest('tr');
            if ($(this).hasClass('debit-input') && parseFloat($(this).val()) > 0) {
                row.find('.credit-input').val('0.00');
            } else if ($(this).hasClass('credit-input') && parseFloat($(this).val()) > 0) {
                row.find('.debit-input').val('0.00');
            }
            updateTotals();
        });

        $('#addRow').click(function() {
            let newRow = `
                <tr class="item-row">
                    <td>
                        <select name="items[${rowCount}][account_type]" class="form-select account-type" required>
                            <option value="General">General Ledger</option>
                            <option value="Supplier">Supplier Account</option>
                            <option value="Expense">Expense Account</option>
                        </select>
                    </td>
                    <td>
                        <select name="items[${rowCount}][account_id]" class="form-select account-select" style="display: none;">
                            <option value="">-- Select Supplier --</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                            @endforeach
                        </select>
                        <span class="text-muted account-placeholder">N/A</span>
                    </td>
                    <td>
                        <input type="number" name="items[${rowCount}][debit]" class="form-control debit-input" step="0.01" value="0.00" required>
                    </td>
                    <td>
                        <input type="number" name="items[${rowCount}][credit]" class="form-control credit-input" step="0.01" value="0.00" required>
                    </td>
                    <td>
                        <input type="text" name="items[${rowCount}][description]" class="form-control" placeholder="Optional row desc">
                    </td>
                    <td>
                        <button type="button" class="btn btn-sm btn-danger remove-row">
                            <i class="fe fe-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
            $('#journalBody').append(newRow);
            rowCount++;
        });

        $(document).on('click', '.remove-row', function() {
            $(this).closest('tr').remove();
            updateTotals();
        });

        // Initial check
        updateTotals();
    });
</script>
@endpush
@endsection
