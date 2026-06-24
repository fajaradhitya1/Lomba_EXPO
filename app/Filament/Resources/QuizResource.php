<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuizResource\Pages;
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
use Illuminate\Database\Eloquent\Builder;

class QuizResource extends Resource
{
    protected static ?string $model = Module::class;

    protected static ?string $navigationIcon = 'heroicon-o-question-mark-circle';
    protected static ?string $navigationLabel = 'Daftar Kuis';
    protected static ?string $pluralLabel = 'Kuis';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('type', 'quiz');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informasi Kuis')
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255),
                        
                        Select::make('course_id')
                            ->label('Mata Kuliah')
                            ->relationship('course', 'name')
                            ->required()
                            ->preload()
                            ->searchable(),
                    ]),

                Section::make('Daftar Soal')
                    ->schema([
                        Repeater::make('quiz_questions')
                            ->label('Soal Kuis')
                            ->schema([
                                TextInput::make('question')
                                    ->required()
                                    ->label('Pertanyaan'),
                                
                                // UPDATE: Menggunakan separator(null) agar tidak pakai koma
                                TagsInput::make('options')
                                    ->required()
                                    ->label('Pilihan Jawaban (Tekan Enter untuk menambah)')
                                    ->separator(null), 
                                
                                TextInput::make('correct_answer')
                                    ->required()
                                    ->label('Jawaban Benar'),
                            ])
                            ->columns(1)
                            ->createItemButtonLabel('Tambah Soal'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Judul Kuis')
                    ->searchable(),
                Tables\Columns\TextColumn::make('course.name')
                    ->label('Mata Kuliah')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function mutateFormDataBeforeCreate(array $data): array
    {
        $data['type'] = 'quiz';
        return $data;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQuizzes::route('/'),
            'create' => Pages\CreateQuiz::route('/create'),
            'edit' => Pages\EditQuiz::route('/{record}/edit'),
        ];
    }
}