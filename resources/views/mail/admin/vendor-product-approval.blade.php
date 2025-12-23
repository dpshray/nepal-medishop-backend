@php
    $approval_status = $product_vendor->is_approved ? 'Approved' : 'Rejected';
    $product_name = $product_vendor->product->name;
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Product {{ $approval_status }} – {{ $product_name }}</title>
</head>
<body style="margin:0; padding:0; background-color:#f4f6f8; font-family: Arial, Helvetica, sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f6f8; padding:20px 0;">
    <tr>
        <td align="center">
            <table width="600" cellpadding="0" cellspacing="0" style="background-color:#ffffff; border-radius:8px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,0.05);">

                <tr>
                    <td style="background-color:#4f46e5; padding:20px;">
                        <h1 style="margin:0; font-size:20px; color:#ffffff;">
                            {{ config('app.name') }} Vendor Notification
                        </h1>
                        <p style="margin:6px 0 0; font-size:14px; color:#e0e7ff;">
                            Product {{ $approval_status }}
                        </p>
                    </td>
                </tr>

                <tr>
                    <td style="padding:24px; color:#1f2937; font-size:14px; line-height:1.6;">
                        <p style="margin-top:0;">Hello <strong>{{ $product_vendor->vendor->user->name }}</strong>,</p>

                        <p>
                            We would like to inform you that your product submission has been
                            <strong style="color: {{ $approval_status === 'Approved' ? '#16a34a' : '#dc2626' }};">
                                {{ $approval_status }}
                            </strong>
                            by our admin team.
                        </p>

                        <h2 style="font-size:16px; margin:20px 0 10px; color:#111827;">
                            🧾 Product Details
                        </h2>

                        <table width="100%" cellpadding="6" cellspacing="0" style="border:1px solid #e5e7eb; border-radius:6px;">
                            <tr>
                                <td style="background-color:#f9fafb; width:40%;"><strong>Product Name</strong></td>
                                <td>{{ $product_name }}</td>
                            </tr>
                            <tr>
                                <td style="background-color:#f9fafb;"><strong>Vendor</strong></td>
                                <td>{{ $product_vendor->vendor->user->name }}</td>
                            </tr>
                        </table>

                        {{-- <h3 style="font-size:15px; margin:20px 0 10px;">
                            Variants & Pricing
                        </h3> --}}

                        {{-- {!! $variants_table !!} --}}

                        @if($approval_status == 1)
                            <div style="margin-top:20px; padding:14px; background-color:#fef2f2; border:1px solid #fecaca; border-radius:6px;">
                                <strong style="color:#dc2626;">❌ Reason for Rejection</strong>
                                <p style="margin:8px 0 0;">{{ $rejection_reason }}</p>
                            </div>
                        @endif

                        @if($approval_status == 0)
                            <div style="margin-top:20px; padding:14px; background-color:#ecfdf5; border:1px solid #a7f3d0; border-radius:6px;">
                                <strong style="color:#16a34a;">✅ Product Approved</strong>
                                <p style="margin:8px 0 0;">
                                    Your product is now live on the platform and visible to customers.
                                </p>
                            </div>
                        @endif

                        <p style="margin-top:24px;">
                            If you have any questions or need clarification, feel free to contact our support team.
                        </p>

                        <p style="margin-bottom:0;">
                            Best regards,<br>
                            <strong>{{ config('app.name') }} Admin Team</strong>
                        </p>
                    </td>
                </tr>

                <tr>
                    <td style="background-color:#f9fafb; padding:16px; text-align:center; font-size:12px; color:#6b7280;">
                        © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
                    </td>
                </tr>

            </table>
        </td>
    </tr>
</table>

</body>
</html>
