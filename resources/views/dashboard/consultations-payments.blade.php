<?php
use App\Models\Utils;
?>
<div class="card mb-4 mb-md-5 border-0 ">
    <div class="card-header p-0 bg-primary rounded-top "
        style="border-top-left-radius: 1rem !important; border-top-right-radius: 1rem !important;">
        <h3 class="px-4 pb-2  text-white py-4 fs-20 fw-700"><b>{{ $title }}</b></h3>
    </div>
    <div class="card-body p-0 ">
        <div class="list-group list-group-flush p-0">

            @if (count($items) == 0)
                <br>
                <h5 class="mb-1 text-center mt-4"><b>No {{ $title }}. </b></h5>
                <br>
            @else
                @foreach ($items as $item)
                    <hr class="p-0 m-0">
                    <a href="{{ admin_url('consultations/' . $item->id) }}/edit" target="_blank"
                        title="Click to update task"
                        class="list-group-item list-group-item-action flex-column align-items-start py-2">
                        <div class="d-flex w-100 justify-content-between">
                            <h5 class="mb-1"><b>{{-- {{ $item->name }} --}}</b></h5>
                            <small><b class="text-primay">{{ $item->due_to_date }}</b></small>
                        </div>
                        <p class="mb-1">
                            UGX {{ number_format($item->total_due) }}
                        </p>
                        <small
                            class="text-muted">{{ $item->consultation_number . ', ' . $item->patient_name . '.' }}</small>
                    </a>
                @endforeach
            @endif
            {{-- 
    "id" => 1
    "created_at" => "2024-07-16 23:07:17"
    "updated_at" => "2024-08-07 00:02:12"
    "patient_id" => 58
    "receptionist_id" => 2
    "company_id" => 1
    "main_status" => "Payment"
    "patient_name" => "Joel Hughes"
    "patient_contact" => "+1 (164) 301-4872"
    "contact_address" => "Ntinda, Kisaasi, Uganda"
    "consultation_number" => "2024-07-16-1"
    "preferred_date_and_time" => null
    "services_requested" => "Antenatal care,Delivery"
    "reason_for_consultation" => "Ab aut est debitis a"
    "main_remarks" => null
    "request_status" => "Pending"
    "request_date" => "2024-07-24 00:00:00"
    "request_remarks" => "Some messages"
    "receptionist_comment" => "Some remarks"
    "temperature" => "10"
    "weight" => "10"
    "height" => "10"
    "bmi" => "30"
    "total_charges" => 74000
    "total_paid" => 1000
    "total_due" => 73000
    "payemnt_status" => "Not Paid"
    "subtotal" => "79200.00"
    "fees_total" => "67000.00"
    "discount" => "5200.00"
    "invoice_processed" => "Yes"
    "invoice_pdf" => "files/2024-07-16-1.pdf"
    "invoice_process_date" => "2024-07-30 18:21:13"
    "bill_status" => "Ready for Billing"
    "specify_specialist" => "No"
    "specialist_id" => null
    "report_link" => null
    "dosage_progress" => null
    "dosage_is_completed" => "No"
--}}


        </div>
    </div>
</div>
