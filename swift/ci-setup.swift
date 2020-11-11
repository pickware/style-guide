#!/usr/bin/swift

// Disable some swiftlint rules, which are too annoying when programming for the CLI
// swiftlint:disable explicit_acl prefixed_toplevel_constant

import Foundation

let swiftLintConfigUrl = URL(fileURLWithPath: ".swiftlint.yml")
let swiftLintConfigBackupUrl = URL(fileURLWithPath: ".swiftlint.yml.bak")

// Read the contents of the SwiftLint configuration
guard var swiftLintConfigContents = try String(bytes: Data(contentsOf: swiftLintConfigUrl), encoding: .utf8) else {
    fatalError("Failed to read file at path '\(swiftLintConfigUrl.relativeString)'.")
}

// Rename the config file to back it up
try FileManager.default.moveItem(at: swiftLintConfigUrl, to: swiftLintConfigBackupUrl)

// Promote SwiftLint's `todo` rule to `error` severity
swiftLintConfigContents += """
todo:
  severity: error

"""

// Save the updated SwiftLint config to the original location
try swiftLintConfigContents.write(to: swiftLintConfigUrl, atomically: false, encoding: .utf8)

// Ignore all frameworks when linting
let frameworksDirectoryPath = "./Frameworks"
if FileManager.default.fileExists(atPath: frameworksDirectoryPath) {
    // Find all sub directories
    let frameworksDirectoryUrl = URL(fileURLWithPath: frameworksDirectoryPath)
    let subDirectoryNames = try FileManager.default
        .contentsOfDirectory(atPath: frameworksDirectoryPath)
        .filter {
            var isDirectory = ObjCBool(false)
            let exists = FileManager.default.fileExists(
                atPath: frameworksDirectoryUrl.appendingPathComponent($0).relativeString,
                isDirectory: &isDirectory
            )

            return exists && isDirectory.boolValue
        }

    // Add a `.swiftlint.yml` to the frameworks directory to exclude all sub directories
    let excludeList = subDirectoryNames
        .map { "  - '\($0)'" }
        .joined(separator: "\n")
    let configContents = "excluded:\n\(excludeList)\n"
    try configContents.write(
        to: URL(fileURLWithPath: frameworksDirectoryPath).appendingPathComponent(".swiftlint.yml"),
        atomically: false,
        encoding: .utf8
    )
}
