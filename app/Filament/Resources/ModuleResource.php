<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ModuleResource\Pages;
use App\Models\Module;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\FileUpload;
use Illuminate\Database\Eloquent\Builder;

class ModuleResource extends Resource
{
    protected static ?string $model = Module::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Informasi Modul')
                ->schema([
                    TextInput::make('title')
                        ->required()
                        ->maxLength(255),
                        TextInput::make('pertemuan')
            ->label('Nomor Pertemuan')
            ->numeric()
            ->required()
            ->default(1)
            ->helperText('Masukkan urutan pertemuan (Contoh: 1, 2, 3)'),
                        
                    Select::make('type')
                        ->options([
                            'module' => 'PDF', 
                            'quiz' => 'Quiz'
                        ])
                        ->live()
                        ->required(),
                    
                    // Filter Semester (Hanya membantu filter, tidak disimpan ke database)
                    Select::make('semester_filter')
                        ->label('Filter Semester')
                        ->options([
                            1 => 'Semester 1', 2 => 'Semester 2', 3 => 'Semester 3',
                            4 => 'Semester 4', 5 => 'Semester 5', 6 => 'Semester 6',
                            7 => 'Semester 7', 8 => 'Semester 8',
                        ])
                        ->placeholder('Pilih Semester untuk memfilter')
                        ->live() // Wajib ada agar dropdown course terupdate otomatis
                        ->dehydrated(false), // Wajib agar tidak disimpan ke tabel modules

                    // Dropdown Mata Kuliah yang difilter
                    Select::make('course_id')
                        ->label('Pilih Mata Kuliah')
                        ->relationship('course', 'name', modifyQueryUsing: function (Builder $query, Forms\Get $get) {
                            // Cek jika filter semester dipilih
                            if ($semester = $get('semester_filter')) {
                                $query->where('semester', $semester);
                            }
                        })
                        ->required()
                        ->preload()
                        ->live(), // Tambahkan live agar form tetap responsif
                ]),

            Section::make('Konten Modul (PDF)')
                ->visible(fn (Forms\Get $get) => $get('type') === 'module')
                ->schema([
                    FileUpload::make('pdf_file')
                        ->label('Upload File PDF')
                        ->directory('modules/pdfs') 
                        ->acceptedFileTypes(['application/pdf'])
                        ->maxSize(5120) // 5MB
                        ->required(),
                ]),

            Section::make('Daftar Soal Kuis')
                ->visible(fn (Forms\Get $get) => $get('type') === 'quiz')
                ->schema([
                    Repeater::make('quiz_questions')
                        ->schema([
                            TextInput::make('question')->required(),
                            TagsInput::make('options')->required(),
                            TextInput::make('correct_answer')->required(),
                        ])
                        ->columns(1)
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable(),
                Tables\Columns\TextColumn::make('type')->badge(),
                Tables\Columns\TextColumn::make('course.name')->label('Mata Kuliah'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListModules::route('/'),
            'create' => Pages\CreateModule::route('/create'),
            'edit' => Pages\EditModule::route('/edit/{record}'),
        ];
    }
}