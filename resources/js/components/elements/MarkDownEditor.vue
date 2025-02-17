<template>
    <MdEditor>
        <template #defToolbars>
            <NormalToolbar title="mark" @onClick="handler">
                <template #trigger>
                    <svg class="md-editor-icon" aria-hidden="true">
                        <use xlink:href="#md-editor-icon-mark"></use>
                    </svg>
                </template>
            </NormalToolbar>
        </template>
    </MdEditor>
</template>

<script setup lang="ts">
import { MdEditor, NormalToolbar } from 'md-editor-v3';
import 'md-editor-v3/lib/style.css';

const handler = () => {
    console.log('NormalToolbar clicked!');
};
</script>