/**
 * SuiteCRM is a customer relationship management program developed by SalesAgility Ltd.
 * Copyright (C) 2021 SalesAgility Ltd.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY SALESAGILITY, SALESAGILITY DISCLAIMS THE
 * WARRANTY OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU Affero General Public License for more
 * details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * In accordance with Section 7(b) of the GNU Affero General Public License
 * version 3, these Appropriate Legal Notices must retain the display of the
 * "Supercharged by SuiteCRM" logo. If the display of the logos is not reasonably
 * feasible for technical reasons, the Appropriate Legal Notices must display
 * the words "Supercharged by SuiteCRM".
 */

import {BaseFieldComponent} from './base-field.component';
import {Component, OnDestroy, OnInit} from '@angular/core';
import {Subscription} from 'rxjs';
import {Field, FieldDefinition, Option} from 'common';
import {DataTypeFormatter} from '../../services/formatters/data-type.formatter.service';
import {LanguageListStringMap, LanguageStore, LanguageStringMap} from '../../store/language/language.store';

@Component({template: ''})
export class BaseEnumComponent extends BaseFieldComponent implements OnInit, OnDestroy {
    selectedValues: Option[] = [];
    valueLabel = '';
    optionsMap: LanguageStringMap;
    options: Option[] = [];
    labels: LanguageStringMap;
    protected subs: Subscription[] = [];
    protected mappedOptions: { [key: string]: Option[] };
    protected isDynamicEnum: boolean = false;

    constructor(protected languages: LanguageStore, protected typeFormatter: DataTypeFormatter) {
        super(typeFormatter);
    }

    ngOnInit(): void {

        if (this.field.metadata && this.field.metadata.options$) {
            this.subs.push(this.field.metadata.options$.subscribe((options: Option[]) => {
                this.buildProvidedOptions(options);

                this.initValue();

            }));
            return;

        }

        if (this.field.definition && this.field.definition.options) {
            this.subs.push(this.languages.appListStrings$.subscribe((appStrings: LanguageListStringMap) => {

                this.buildAppStringListOptions(appStrings);
                this.initValue();

            }));
        }

    }

    ngOnDestroy(): void {
        this.isDynamicEnum = false;
        this.subs.forEach(sub => sub.unsubscribe());
    }

    getInvalidClass(): string {
        if (this.field.formControl && this.field.formControl.invalid && this.field.formControl.touched) {
            return 'is-invalid';
        }
        return '';
    }

    protected buildProvidedOptions(options: Option[]): void {
        this.options = options;
        this.optionsMap = {};

        options.forEach(option => {
            this.optionsMap[option.value] = option.label;
        });

    }

    protected buildAppStringListOptions(appStrings: LanguageListStringMap): void {

        if (!appStrings || !this.field.definition.options || !appStrings[this.field.definition.options]) {
            return;
        }
        this.optionsMap = appStrings[this.field.definition.options] as LanguageStringMap;

        if (!this.optionsMap || !Object.keys(this.optionsMap)) {
            return;
        }
        this.buildOptionsArray(appStrings);
    }

    protected buildOptionsArray(appStrings: LanguageListStringMap): void {

        this.options = [];
        Object.keys(this.optionsMap).forEach(key => {

            this.options.push({
                value: key,
                label: this.optionsMap[key]
            });
        });

        if (this.isDynamicEnum) {
            this.buildDynamicEnumOptions(appStrings);
        }
    }

    protected initValue(): void {
        if (typeof this.field.value !== 'string') {
            return;
        }

        if (!this.optionsMap) {
            return;
        }

        this.selectedValues = [];

        if (typeof this.optionsMap[this.field.value] !== 'string') {
            return;
        }

        if (this.field.value) {
            this.valueLabel = this.optionsMap[this.field.value];
            this.selectedValues.push({
                value: this.field.value,
                label: this.valueLabel
            });
        }
    }

    checkAndInitAsDynamicEnum() {

        const definition = (this.field && this.field.definition) || {} as FieldDefinition;
        const dynamic = (definition && definition.dynamic) || false;
        const parentEnumKey = (definition && definition.parentenum) || '';
        const fields = (this.record && this.record.fields) || null;

        if (dynamic && parentEnumKey && fields) {
            this.isDynamicEnum = true;
            const parentEnum: Field = fields[parentEnumKey];
            if (parentEnum) {
                this.subscribeToParentValueChanges(parentEnum);
            }
        }
    }

    buildDynamicEnumOptions(appStrings: LanguageListStringMap) {

        const parentEnum = this.record.fields[this.field.definition.parentenum];

        if (parentEnum) {

            const parentOptionMap: LanguageStringMap = appStrings[parentEnum.definition.options] as LanguageStringMap;

            if (parentOptionMap && Object.keys(parentOptionMap).length != 0) {

                this.mappedOptions = this.createParentChildOptionsMap(parentOptionMap, this.options);

                let parentValues: string[] = [];
                if (parentEnum.definition.type === 'multienum') {
                    parentValues = parentEnum.valueList;
                } else {
                    parentValues.push(parentEnum.value);
                }
                this.options = this.filterMatchingOptions(parentValues);

            }
        }
    }

    filterMatchingOptions(values: string[]): Option[] {

        let filteredOptions: Option[] = [];

        if (!values || !values.length) {
            return [];
        }

        values.forEach(value => {
            if (!this.mappedOptions[value]) {
                return;
            }
            filteredOptions = filteredOptions.concat([...this.mappedOptions[value]]);
        });

        return filteredOptions;
    }

    createParentChildOptionsMap(parentOptions: LanguageStringMap, childOptions: Option[]): { [key: string]: Option[] } {
        const mappedOptions: { [key: string]: Option[] } = {};
        Object.keys(parentOptions).forEach(key => {
                mappedOptions[key] = childOptions.filter(
                    option => String(option.value).startsWith(parentOptions[key])
                )
            }
        );
        return mappedOptions;
    }

    subscribeToParentValueChanges(parentEnum: Field) {
        if (parentEnum.formControl) {
            this.subs.push(parentEnum.formControl.valueChanges.subscribe(values => {

                if (typeof values === 'string') {
                    values = [values];
                }

                // Reset selected values on Form Control
                this.field.value = '';
                this.field.formControl.setValue('');

                // Rebuild available enum options
                this.options = this.filterMatchingOptions(values);

                this.initValue();
            }));
        }
    }

}
