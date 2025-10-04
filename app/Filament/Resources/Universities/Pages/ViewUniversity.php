<?php

namespace App\Filament\Resources\Universities\Pages;

use App\Filament\Resources\Universities\UniversityResource;
use App\Services\UniversityPdfExportService;
use App\Services\ProgramPdfExportService;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;

class ViewUniversity extends ViewRecord
{
    protected static string $resource = UniversityResource::class;

    protected static ?string $title = 'University Details';

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('edit')
                ->label('Edit University')
                ->icon('heroicon-o-pencil')
                ->color('primary')
                ->url(fn () => route('filament.admin.resources.universities.edit', $this->record)),

                    Actions\Action::make('export_pdf')
                        ->label('Export University PDF')
                        ->icon('heroicon-o-document-arrow-down')
                        ->color('success')
                        ->action(function ($record) {
                            try {
                                $pdfService = new UniversityPdfExportService;
                                $pdf = $pdfService->generatePdf($record);

                                $filename = 'university-report-'.str()->slug($record->name).'-'.now()->format('Y-m-d').'.pdf';

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

                    Actions\Action::make('export_programs_pdf')
                        ->label('Export Programs PDF')
                        ->icon('heroicon-o-book-open')
                        ->color('info')
                        ->action(function ($record) {
                            try {
                                $pdfService = new ProgramPdfExportService;
                                $pdf = $pdfService->generateUniversityProgramsPdf($record);

                                $filename = 'university-programs-'.str()->slug($record->name).'-'.now()->format('Y-m-d').'.pdf';

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

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                \Filament\Schemas\Components\Section::make()
                    ->schema([
                        \Filament\Schemas\Components\View::make('university.custom-details')
                            ->viewData(function () {
                                return ['university' => $this->record];
                            }),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
