<?php
$caseNumber = str_pad($dispute['case_id'], 8, '0', STR_PAD_LEFT);
$createdDate = date('F j, Y', strtotime($dispute['created_at']));
$responseDeadline = date('F j, Y', strtotime($dispute['response_deadline']));
$checkIn = date('F j, Y', strtotime($reservation['check_in']));
$checkOut = date('F j, Y', strtotime($reservation['check_out']));

$statusColors = [
    'pending' => '#ffc107',
    'in_review' => '#17a2b8',
    'resolved' => '#28a745',
    'escalated' => '#dc3545'
];

$statusColor = $statusColors[$dispute['status']] ?? '#6c757d';

$content = <<<HTML
<div style="text-align: center; padding: 20px;">
    <h1>Dispute Resolution Case #{$caseNumber}</h1>
    <p style="font-size: 18px; color: #666;">
        We're here to help resolve this matter fairly and efficiently.
    </p>
</div>

<div class="info">
    <h3>Case Details</h3>
    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            <td><strong>Case Number:</strong></td>
            <td>#{$caseNumber}</td>
        </tr>
        <tr>
            <td><strong>Filed Date:</strong></td>
            <td>{$createdDate}</td>
        </tr>
        <tr>
            <td><strong>Status:</strong></td>
            <td>
                <span style="color: {$statusColor}; font-weight: bold; text-transform: uppercase;">
                    {$dispute['status']}
                </span>
            </td>
        </tr>
        <tr>
            <td><strong>Response Deadline:</strong></td>
            <td style="color: #dc3545;">{$responseDeadline}</td>
        </tr>
    </table>
</div>

<div class="info" style="margin-top: 20px;">
    <h3>Reservation Details</h3>
    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            <td><strong>Property:</strong></td>
            <td>{$property['title']}</td>
        </tr>
        <tr>
            <td><strong>Check-in:</strong></td>
            <td>{$checkIn}</td>
        </tr>
        <tr>
            <td><strong>Check-out:</strong></td>
            <td>{$checkOut}</td>
        </tr>
        <tr>
            <td><strong>Booking ID:</strong></td>
            <td>{$reservation['id']}</td>
        </tr>
    </table>
</div>

<div class="info" style="margin-top: 20px;">
    <h3>Dispute Summary</h3>
    <p style="background-color: #f8f9fa; padding: 15px; border-radius: 4px;">
        {$dispute['description']}
    </p>
</div>

<div class="info" style="margin-top: 20px;">
    <h3>Required Actions</h3>
    <ul style="list-style-type: none; padding-left: 0;">
HTML;

foreach ($dispute['required_actions'] as $action) {
    $content .= <<<HTML
        <li style="margin-bottom: 15px; padding-left: 25px; position: relative;">
            <span style="position: absolute; left: 0; color: #dc3545;">⚠️</span>
            {$action}
        </li>
HTML;
}

$content .= <<<HTML
    </ul>
</div>

<div style="text-align: center; margin: 30px 0;">
    <a href="<?= SITE_URL ?>/disputes/{$dispute['case_id']}" class="button" style="margin-right: 10px;">
        View Case Details
    </a>
    <a href="<?= SITE_URL ?>/disputes/{$dispute['case_id']}/respond" class="button" style="background-color: #28a745;">
        Submit Response
    </a>
</div>

<div class="info">
    <h3>Resolution Process</h3>
    <ol>
        <li>Review the dispute details carefully</li>
        <li>Provide requested information or evidence</li>
        <li>Submit your response before the deadline</li>
        <li>Wait for our resolution team's review</li>
        <li>Receive the final decision</li>
    </ol>
</div>

<div class="info" style="margin-top: 20px;">
    <h3>Evidence Guidelines</h3>
    <ul>
        <li>Submit clear, dated photographs</li>
        <li>Include any relevant messages or communication</li>
        <li>Provide receipts or documentation if applicable</li>
        <li>Ensure all files are legible and properly oriented</li>
        <li>Submit evidence in common file formats (JPG, PDF, PNG)</li>
    </ul>
</div>

<div style="margin: 30px 0; padding: 20px; background-color: #fff3cd; border-radius: 4px;">
    <p style="color: #856404; margin: 0;">
        <strong>Important:</strong> Please respond by {$responseDeadline}. 
        Failure to respond may result in an automatic decision based on available information.
    </p>
</div>

<div style="margin-top: 30px;">
    <h3>Mediation Support</h3>
    <p>Our dispute resolution team is here to help:</p>
    <ul>
        <li>Email: disputes@airbnbclone.com</li>
        <li>Phone: 1-800-RESOLVE</li>
        <li>Live Chat: Available on the dispute resolution page</li>
        <li>Response Time: Within 24 hours</li>
    </ul>
</div>

<div class="info" style="margin-top: 20px;">
    <h3>Tips for Quick Resolution</h3>
    <ul>
        <li>Respond promptly to all requests</li>
        <li>Provide clear, factual information</li>
        <li>Stay professional and courteous</li>
        <li>Be open to compromise</li>
        <li>Keep all communication within the platform</li>
    </ul>
</div>

<div style="margin-top: 20px; text-align: center; font-size: 12px; color: #666;">
    <p>
        This is an official communication regarding dispute case #{$caseNumber}.<br>
        Please do not reply to this email. Use the dispute resolution center for all responses.
    </p>
</div>
HTML;

include __DIR__ . '/layout.php';
?>
