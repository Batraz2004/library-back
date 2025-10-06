<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoriesResource\Pages;
use App\Filament\Resources\CategoriesResource\RelationManagers;
use App\Models\Categories;
use Filament\Actions\StaticAction;
use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CategoriesResource extends Resource
{
    protected static ?string $model = Categories::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('title'),
                TextInput::make('description'),
                Repeater::make('fields')
                    ->label('Дополнительные Модули')
                    ->addActionLabel('Добавить запись в список')
                    ->deleteAction(
                        fn(StaticAction $action) => $action->label('Удалить запись'),
                    )
                    ->schema([
                        TextInput::make('data.text')->label('текст'),
                        Toggle::make('data.require_field')->default(true)->label('обязательное поле'),
                        Select::make('data.type')
                            ->options([
                                'list' => 'List',
                                'text' => 'Text',
                                'number' => 'Number',
                                'decimal' => 'DECIMAL',
                            ])
                            ->live()//что бы обновлялась страница если выбрал тип list 
                            ->default('text')
                            ->label('тип поля'),
                            Repeater::make('field_list')
                                ->label('список')
                                ->addActionLabel('Добавить запись в список')
                                ->deleteAction(
                                    fn(StaticAction $action) => $action->label('Удалить запись'),
                                )
                                ->schema([
                                    TextInput::make('data.list.text')->label('текст'),
                                ])
                            ->visible(function($get){
                                return $get('data.type') === 'list';
                            })

                    ])

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title'),
            ])
            ->filters([
                //
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategories::route('/create'),
            'edit' => Pages\EditCategories::route('/{record}/edit'),
        ];
    }
}
