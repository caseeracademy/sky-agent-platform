<?php

namespace App\Filament\Resources\Programs\Pages;

use App\Filament\Resources\Programs\ProgramResource;
use App\Services\ProgramPdfExportService;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;

class ViewProgram extends ViewRecord
{
    protected static string $resource = ProgramResource::class;

    protected static ?string $title = 'Program Details';

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('edit')
                ->label('Edit Program')
                ->color('primary')
                ->url(fn () => route('filament.admin.resources.programs.edit', $this->record)),

            Actions\Action::make('export_pdf')
                ->label('Export PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->action(function ($record) {
                    try {
                        $pdfService = new ProgramPdfExportService;
                        $pdf = $pdfService->generateProgramPdf($record);

                        $filename = 'program-report-'.str()->slug($record->name).'-'.now()->format('Y-m-d').'.pdf';

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
                        \Filament\Schemas\Components\View::make('program.custom-details')
                            ->viewData(function () {
                                return ['program' => $this->record];
                            }),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
