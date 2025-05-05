<?php
namespace App\Mail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Barryvdh\DomPDF\Facade\Pdf;

class DailyReport extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public $yourPdfData;
    public $igungaBranch;
    public $katoroBranch;
    public function __construct($data,$igungaBranch,$katoroBranch)
    {
        $this->yourPdfData=$data;
        $this->igungaBranch=$igungaBranch;
        $this->katoroBranch=$katoroBranch;

    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Daily Report',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.daily_report',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }

    public function build() {

    $pdf = PDF::loadView('daily_report',$this->yourPdfData)->setPaper('a4', 'landscape');

    $pdfData = $pdf->output();

    $pdf1 = PDF::loadView('daily_report1',$this->igungaBranch)->setPaper('a4', 'landscape');

    $pdfData1 = $pdf1->output();

    $pdf2 = PDF::loadView('daily_report2',$this->katoroBranch)->setPaper('a4', 'landscape');

    $pdfData2 = $pdf2->output();

    return $this->view('emails.daily_report')
                ->attachData($pdfData, 'daily_report.pdf', [
                    'mime' => 'application/pdf',
                ])
                ->attachData($pdfData1, 'daily_report.pdf', [
                    'mime' => 'application/pdf',
                ])
                ->attachData($pdfData2, 'daily_report.pdf', [
                    'mime' => 'application/pdf',
                ]);
        }
}
