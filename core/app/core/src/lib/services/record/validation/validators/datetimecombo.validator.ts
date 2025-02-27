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

import {ValidatorInterface} from '../validator.Interface';
import {AbstractControl} from '@angular/forms';
import {Record} from 'common';
import {ViewFieldDefinition} from 'common';
import {Injectable} from '@angular/core';
import {DatetimeFormatter} from '../../../formatters/datetime/datetime-formatter.service';
import {StandardValidationErrors, StandardValidatorFn} from 'common';

export const dateTimeComboValidator = (formatter: DatetimeFormatter): StandardValidatorFn => (
    (control: AbstractControl): StandardValidationErrors | null => {

        const invalid = formatter.validateUserFormat(control.value);
        return invalid ? {
            dateTimeValidator: {
                value: control.value,
                message: {
                    labelKey: 'LBL_VALIDATION_ERROR_DATETIME_FORMAT',
                    context: {
                        value: control.value,
                        expected: formatter.toUserFormat('2020-01-23 12:30:40')
                    }
                }
            },
        } : null;
    }
);

@Injectable({
    providedIn: 'root'
})
export class DateTimeComboValidator implements ValidatorInterface {

    constructor(protected formatter: DatetimeFormatter) {
    }

    applies(record: Record, viewField: ViewFieldDefinition): boolean {
        if (!viewField || !viewField.fieldDefinition) {
            return false;
        }

        return viewField.type === 'datetimecombo';
    }

    getValidator(viewField: ViewFieldDefinition): StandardValidatorFn[] {

        if (!viewField || !viewField.fieldDefinition) {
            return [];
        }

        return [dateTimeComboValidator(this.formatter)];
    }
}
