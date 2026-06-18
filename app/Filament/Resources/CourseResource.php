<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CourseResource\Pages;
use App\Models\Course;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;

class CourseResource extends Resource
{
    protected static ?string $model = Course::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('name')
                ->label('Nama Mata Kuliah')
                ->required()
                ->maxLength(255),

            Forms\Components\Select::make('semester')
                ->label('Semester')
                ->options([
                    1 => 'Semester 1',
                    2 => 'Semester 2',
                    3 => 'Semester 3',
                    4 => 'Semester 4',
                    5 => 'Semester 5',
                    6 => 'Semester 6',
                    7 => 'Semester 7',
                    8 => 'Semester 8',
                ])
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Mata Kuliah')
                    ->searchable(),

                TextColumn::make('semester')
                    ->label('Semester'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCourses::route('/'),
            'create' => Pages\CreateCourse::route('/create'),
            'edit' => Pages\EditCourse::route('/{record}/edit'),
        ];
    }
}