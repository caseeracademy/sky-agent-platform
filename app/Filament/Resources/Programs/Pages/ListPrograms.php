<?php

namespace App\Filament\Resources\Programs\Pages;

use App\Filament\Resources\Programs\ProgramResource;
use App\Services\ProgramPdfExportService;
use Filament\Actions;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPrograms extends ListRecords
{
    protected static string $resource = ProgramResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            
                    Actions\Action::make('export_all_programs_pdf')
                        ->label('Export All Programs PDF')
                        ->icon('heroicon-o-document-arrow-down')
                        ->color('success')
                        ->action(function () {
                            try {
                                $programs = \App\Models\Program::with(['university', 'degree', 'applications'])->get();
                                
                                $html = view('pdf.all-programs-report', [
                                    'programs' => $programs,
                                    'stats' => [
                                        'total_programs' => $programs->count(),
                                        'active_programs' => $programs->where('is_active', true)->count(),
                                        'total_universities' => $programs->pluck('university_id')->unique()->count(),
                                        'total_applications' => $programs->sum(fn($p) => $p->applications->count()),
                                        'success_rate' => 0,
                                    ]
                                ])->render();

                                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html)
                                    ->setPaper('a4', 'portrait')
                                    ->setOptions([
                                        'isHtml5ParserEnabled' => true,
                                        'isRemoteEnabled' => true,
                                        'defaultFont' => 'Arial',
                                    ]);

                                $filename = 'all-programs-report-'.now()->format('Y-m-d').'.pdf';

                                return response()->streamDownload(
                                    fn () => print ($pdf->output()),
                                    $filename,
                                    ['Content-Type' => 'application/pdf']
                                );
                            } catch (\Exception $e) {
                                \Filament\Notifications\Notification::make()
                                    ->title('Export Failed')
                                    ->body('Failed to generate PDF: '.$e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }),
        ];
    }
}
