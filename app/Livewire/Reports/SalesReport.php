<?php

namespace App\Livewire\Reports;

use Livewire\Component;
use Livewire\WithPagination;
use Modules\Sale\Entities\Sale;
use Barryvdh\DomPDF\Facade\Pdf;
use Modules\Expense\Entities\Expense;
use Modules\SalesReturn\Entities\SaleReturn;



class SalesReport extends Component
{

    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $customers;
    public $start_date;
    public $end_date;
    public $customer_id;
    public $sale_status;
    public $payment_status;



    protected $rules = [
        'start_date' => 'required|date|before_or_equal:end_date',
        'end_date'   => 'required|date|after_or_equal:start_date',
    ];


    public function mount($customers)
    {
        $this->customers = $customers;
        $this->start_date = today()->subDays(30)->format('Y-m-d');
        $this->end_date = today()->format('Y-m-d');
        $this->customer_id = '';
        $this->sale_status = '';
        $this->payment_status = '';
    }

    public function render()
    {
        $sales = Sale::with(['saleDetails.product']) // 👈 eager load
            ->whereDate('date', '>=', $this->start_date)
            ->whereDate('date', '<=', $this->end_date)
            ->when($this->customer_id, fn($q) => $q->where('customer_id', $this->customer_id))
            ->when($this->sale_status, fn($q) => $q->where('status', $this->sale_status))
            ->when($this->payment_status, fn($q) => $q->where('payment_status', $this->payment_status))
            ->orderBy('date', 'desc')
            ->paginate(10);

        return view('livewire.reports.sales-report', compact('sales'));
    }

    public function generateReport()
    {
        $this->validate();
        $this->render();
    }


    public function generatePdf()
    {
        $this->validate();

        $sales = Sale::with(['saleDetails.product'])
            ->whereDate('date', '>=', $this->start_date)
            ->whereDate('date', '<=', $this->end_date)
            ->when($this->customer_id, fn($q) => $q->where('customer_id', $this->customer_id))
            ->when($this->sale_status, fn($q) => $q->where('status', $this->sale_status))
            ->when($this->payment_status, fn($q) => $q->where('payment_status', $this->payment_status))
            ->orderBy('date', 'desc')
            ->get(); // ❗ get(), NOT paginate()


        $totalExpenses = Expense::whereDate('date', '>=', $this->start_date)
            ->whereDate('date', '<=', $this->end_date)
            ->sum('amount') / 100;

        $totalSalesReturn = SaleReturn::whereDate('date', '>=', $this->start_date)
            ->whereDate('date', '<=', $this->end_date)
            ->sum('paid_amount') / 100;


        // OPTIONAL: TOTAL SALES PAID
        $totalSales = $sales->sum('paid_amount');
        // OPTIONAL: PROFIT
        $profit = $totalSales - $totalExpenses - $totalSalesReturn;



        $pdf = \PDF::loadView('livewire.reports.sales-pdf', [
            'sales' => $sales,
            'start_date' => $this->start_date,

            'end_date' => $this->end_date,
            'totalExpenses' => $totalExpenses,
            'totalSales'    => $totalSales,
            'profit'        => $profit,
            'totalSalesReturns' => $totalSalesReturn,
        ]);


        return response()->streamDownload(
            fn() => print($pdf->output()),
            "sales-report-{$this->start_date}-to-{$this->end_date}.pdf"
        );
    }
}
