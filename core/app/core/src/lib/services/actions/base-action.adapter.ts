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

import {
    Action,
    ActionContext,
    ActionData,
    ActionDataSource,
    ActionHandler,
    ActionManager,
    ModeActions,
    ViewMode
} from 'common';
import {Observable} from 'rxjs';
import {take} from 'rxjs/operators';
import {AsyncActionInput, AsyncActionService} from '../process/processes/async-action/async-action';
import {MessageService} from '../message/message.service';
import {Process} from '../process/process.service';
import {ConfirmationModalService} from '../modals/confirmation-modal.service';
import {LanguageStore} from '../../store/language/language.store';

export abstract class BaseActionsAdapter<D extends ActionData> implements ActionDataSource {

    defaultActions: ModeActions = {
        detail: [],
        list: [],
        edit: [],
        create: []
    };

    protected constructor(
        protected actionManager: ActionManager<D>,
        protected asyncActionService: AsyncActionService,
        protected message: MessageService,
        protected confirmation: ConfirmationModalService,
        protected language: LanguageStore
    ) {
    }

    abstract getActions(context?: ActionContext): Observable<Action[]>;

    protected abstract getModuleName(context?: ActionContext): string;

    protected abstract reload(action: Action, process: Process, context?: ActionContext): void;

    protected abstract getMode(): ViewMode;

    protected abstract buildActionData(action: Action, context?: ActionContext): D;

    /**
     * Run the action using given context
     * @param action
     * @param context
     */
    runAction(action: Action, context: ActionContext = null): void {
        const params = (action && action.params) || {} as { [key: string]: any };
        const displayConfirmation = params.displayConfirmation || false;
        const confirmationLabel = params.confirmationLabel || '';

        if (displayConfirmation) {
            this.confirmation.showModal(confirmationLabel, () => {
                this.callAction(action, context);
            });

            return;
        }

        this.callAction(action, context);
    }

    /**
     * Build async process input
     * @param action
     * @param actionName
     * @param moduleName
     * @param context
     */
    protected abstract buildActionInput(action: Action, actionName: string, moduleName: string, context?: ActionContext): AsyncActionInput;

    /**
     * Get action name
     * @param action
     */
    protected getActionName(action: Action) {
        return `${action.key}`;
    }

    /**
     * Parse mode actions
     * @param declaredActions
     * @param mode
     * @param context
     */
    protected parseModeActions(declaredActions: Action[], mode: ViewMode, context: ActionContext = null) {
        if (!declaredActions) {
            return [];
        }

        const availableActions = {
            list: [],
            detail: [],
            edit: [],
            create: [],
        } as ModeActions;

        if (declaredActions && declaredActions.length) {
            declaredActions.forEach(action => {
                if (!action.modes || !action.modes.length) {
                    return;
                }

                action.modes.forEach(actionMode => {
                    if (!availableActions[actionMode] && !action.asyncProcess) {
                        return;
                    }
                    availableActions[actionMode].push(action);
                });
            });
        }

        availableActions.detail = availableActions.detail.concat(this.defaultActions.detail);
        availableActions.list = availableActions.list.concat(this.defaultActions.list);
        availableActions.edit = availableActions.edit.concat(this.defaultActions.edit);
        availableActions.create = availableActions.create.concat(this.defaultActions.create);

        const actions = [];

        availableActions[mode].forEach(action => {

            if (!action.asyncProcess) {
                const actionHandler = this.actionManager.getHandler(action, mode);
                const data: D = this.buildActionData(action, context);

                if (!this.shouldDisplay(actionHandler, data)) {
                    return;
                }

                action.status = actionHandler.getStatus(data) || '';
            }

            const module = (context && context.module) || '';
            const label = this.language.getFieldLabel(action.labelKey, module);
            actions.push({
                ...action,
                label
            });
        });

        return actions;
    }

    protected shouldDisplay(actionHandler: ActionHandler<D>, data: D): boolean {

        return actionHandler && actionHandler.shouldDisplay(data);
    }

    /**
     * Call actions
     * @param action
     * @param context
     */
    protected callAction(action: Action, context: ActionContext = null) {
        if (action.asyncProcess) {
            this.runAsyncAction(action, context);
            return;
        }
        this.runFrontEndAction(action, context);
    }

    /**
     * Run async actions
     * @param action
     * @param context
     */
    protected runAsyncAction(action: Action, context: ActionContext = null): void {
        const actionName = this.getActionName(action);
        const moduleName = this.getModuleName(context);

        this.message.removeMessages();
        const asyncData = this.buildActionInput(action, actionName, moduleName, context);

        this.asyncActionService.run(actionName, asyncData).pipe(take(1)).subscribe((process: Process) => {
            if (this.shouldReload(process)) {
                this.reload(action, process, context);
            }
        });
    }

    /**
     * Should reload page
     * @param process
     */
    protected shouldReload(process: Process): boolean {
        return !!(process.data && process.data.reload);
    }

    /**
     * Run front end action
     * @param action
     * @param context
     */
    protected runFrontEndAction(action: Action, context: ActionContext = null): void {
        const data: D = this.buildActionData(action, context);

        this.actionManager.run(action, this.getMode(), data);
    }
}
